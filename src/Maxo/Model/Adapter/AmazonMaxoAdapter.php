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

namespace Amazon\Maxo\Model\Adapter;

/**
 * Class AmazonMaxoAdapter
 */
class AmazonMaxoAdapter
{
    /**
     * @var \Amazon\Maxo\Client\ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var \Amazon\Maxo\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AmazonMaxoAdapter constructor.
     * @param \Amazon\Maxo\Client\ClientFactoryInterface $clientFactory
     * @param \Amazon\Maxo\Model\AmazonConfig $amazonConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Amazon\Maxo\Client\ClientFactoryInterface $clientFactory,
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->amazonConfig = $amazonConfig;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Create new Amazon Checkout Session
     *
     * @param $storeId
     * @return mixed
     */
    public function createCheckoutSession($storeId)
    {
        $payload = [
            'webCheckoutDetails' => [
                'checkoutReviewReturnUrl' => $this->amazonConfig->getCheckoutReviewReturnUrl(),
            ],
            'storeId' => $this->amazonConfig->getClientId(),
        ];

        $headers = [
            'x-amz-pay-idempotency-key' => uniqid(),
        ];

        $response = $this->clientFactory->create($storeId)->createCheckoutSession($payload, $headers);

        if (!$response || (isset($response['response'])
                && strpos($response['response'], 'checkoutSessionId') === false)) {
            $this->logger->debug(__('Unable to create checkout session'));
        } else {
            return json_decode($response['response'], true);
        }
    }

    /**
     * Return checkout session details
     *
     * @param $storeId
     * @param $checkoutSessionId
     * @return mixed
     */
    public function getCheckoutSession($storeId, $checkoutSessionId)
    {
        $response = $this->clientFactory->create($storeId)->getCheckoutSession($checkoutSessionId);

        if (!$response || (isset($response['response'])
                && strpos($response['response'], 'checkoutSessionId') === false)) {
            $this->logger->debug(__('Unable to get checkout session'));
        } else {
            return json_decode($response['response'], true);
        }
    }

    /**
     * Update Checkout Session to set payment info and transaction metadata
     *
     * @param $quote
     * @param $checkoutSessionId
     * @return mixed
     */
    public function updateCheckoutSession($quote, $checkoutSessionId)
    {
        $storeId = $quote->getStoreId();
        $store = $quote->getStore();

        if (!$quote->getReservedOrderId()) {
            try {
                $quote->reserveOrderId()->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $payload = [
            'webCheckoutDetails' => [
                'checkoutResultReturnUrl' => $store->getUrl(
                    'amazon_maxo/payment/completeCheckout',
                    ['_forced_secure' => true]
                )
            ],
            'paymentDetails' => [
                'paymentIntent' => 'Authorize',
                'canHandlePendingAuthorization' => true,
                'chargeAmount' => [
                    'amount' => number_format($quote->getGrandTotal(), 2),
                    'currencyCode' => $store->getCurrentCurrency()->getCode(),
                ],
            ],
            'merchantMetadata' => [
                'merchantReferenceId' => $quote->getReservedOrderId(),
                'merchantStoreName' => $store->getName(),
                //noteToBuyer => '',
                //customInformation => '',
            ]
        ];

        $response = $this->clientFactory->create($storeId)->updateCheckoutSession($checkoutSessionId, $payload);

        if (!$response ||
            (isset($response['response']) && strpos($response['response'], 'checkoutSessionId') === false)) {
            $this->logger->debug(__('Unable to update checkout session'));
        } else {
            return json_decode($response['response'], true);
        }
    }

    /**
     * Create charge
     *
     * @param $storeId
     * @param $chargeId
     * @param $amount
     * @param $currency
     * @return mixed
     */
    public function captureCharge($storeId, $chargeId, $amount, $currency)
    {
        $headers = [
            'x-amz-pay-idempotency-key' => uniqid(),
        ];

        $payload = [
            'captureAmount' => [
                'amount' => $amount,
                'currencyCode' => $currency,
            ]
        ];

        $response = $this->clientFactory->create($storeId)->captureCharge($chargeId, $payload, $headers);

        if (!isset($response['response'])) {
            $this->logger->debug(__('Unable to capture charge'));
        } else {
            return json_decode($response['response'], true);
        }
    }

    /**
     * Cancel charge
     *
     * @param $storeId
     * @param $chargeId
     */
    public function cancelCharge($storeId, $chargeId, $reason = 'ADMIN VOID')
    {
        $payload = [
            'cancellationReason' => $reason
        ];

        $response = $this->clientFactory->create($storeId)->cancelCharge($chargeId, $payload);

        if (!isset($response['response'])) {
            $this->logger->debug(__('Unable to cancel charge'));
        } else {
            return json_decode($response['response'], true);
        }
    }

    /**
     * Authorize Gateway Command
     *
     * @param $data
     */
    public function authorize($data)
    {
        $quote = $this->quoteRepository->get($data['quote_id']);
        $response = $this->getCheckoutSession($quote->getStoreId(), $data['amazon_checkout_session_id']);
        return $response;
    }
}
