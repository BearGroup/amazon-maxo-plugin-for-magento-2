<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Maxo\Model;

use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Webapi\Exception as WebapiException;

class AddressManagement implements \Amazon\Maxo\Api\AddressManagementInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonMaxoAdapter
     */
    private $amazonAdapter;

    /**
     * @var \Amazon\Payment\Helper\Address
     */
    private $addressHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var \Amazon\Core\Domain\AmazonAddressFactory
     */
    private $amazonAddressFactory;

    /**
     * @var \Magento\Framework\Validator\Factory
     */
    private $validatorFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AddressManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonMaxoAdapter $amazonAdapter
     * @param \Amazon\Payment\Helper\Address $addressHelper
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Amazon\Core\Domain\AmazonAddressFactory $amazonAddressFactory
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        \Amazon\Maxo\Model\Adapter\AmazonMaxoAdapter $amazonAdapter,
        \Amazon\Payment\Helper\Address $addressHelper,
        \Magento\Checkout\Model\Session $session,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Amazon\Core\Domain\AmazonAddressFactory $amazonAddressFactory,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
        $this->addressHelper = $addressHelper;
        $this->session = $session;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->amazonAddressFactory = $amazonAddressFactory;
        $this->validatorFactory = $validatorFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress($amazonCheckoutSessionId)
    {
        if (!$this->amazonConfig->isEnabled()) {
            return false;
        }

        try {
            $response = $this->amazonAdapter->getCheckoutSession(
                $this->storeManager->getStore()->getId(),
                $amazonCheckoutSessionId
            );

            if (isset($response['shippingAddress'])) {
                $shippingAddress = $response['shippingAddress'];
                $address = array_combine(
                    array_map('ucfirst', array_keys($shippingAddress)),
                    array_values($shippingAddress)
                );

                $address = $this->convertToMagentoAddress($address, true);
                $address[0]['email'] = $response['buyer']['email'];

                return $address;
            }

        } catch (SessionException $e) {
            throw $e;
        } catch (WebapiException $e) {
            throw $e;
        } catch (ValidatorException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error($e);
            $this->throwUnknownErrorException();
        }
    }

    protected function throwUnknownErrorException()
    {
        throw new WebapiException(
            __('Amazon could not process your request.'),
            0,
            WebapiException::HTTP_INTERNAL_ERROR
        );
    }

    protected function convertToMagentoAddress(array $address, $isShippingAddress = false)
    {
        $amazonAddress  = $this->amazonAddressFactory->create(['address' => $address]);
        $magentoAddress = $this->addressHelper->convertToMagentoEntity($amazonAddress);

        if ($isShippingAddress) {
            $validator = $this->validatorFactory->createValidator('amazon_address', 'on_select');

            if (! $validator->isValid($magentoAddress)) {
                throw new ValidatorException(null, null, [$validator->getMessages()]);
            }

            $countryCollection = $this->countryCollectionFactory->create();

            $collectionSize = $countryCollection->loadByStore()
                ->addFieldToFilter('country_id', ['eq' => $magentoAddress->getCountryId()])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getSize();

            if (1 != $collectionSize) {
                throw new WebapiException(__('the country for your address is not allowed for this store'));
            }
        }

        return [$this->addressHelper->convertToArray($magentoAddress)];
    }
}
