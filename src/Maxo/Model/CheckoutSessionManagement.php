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

use Magento\Quote\Api\CartRepositoryInterface;

class CheckoutSessionManagement implements \Amazon\Maxo\Api\CheckoutSessionManagementInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amazon\Maxo\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Maxo\Model\Adapter\AmazonMaxoAdapter
     */
    private $amazonAdapter;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * CheckoutSessionManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonMaxoAdapter $amazonAdapter
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        \Amazon\Maxo\Model\Adapter\AmazonMaxoAdapter $amazonAdapter,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->storeManager = $storeManager;
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createCheckoutSession()
    {
        if (!$this->amazonConfig->isEnabled()) {
            return false;
        }
        $response = $this->amazonAdapter->createCheckoutSession($this->storeManager->getStore()->getId());
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function completeCheckout($amazonCheckoutSessionId)
    {
        if (!$this->amazonConfig->isEnabled()) {
            return false;
        }
        $response = $this->amazonAdapter->getCheckoutSession(
            $this->storeManager->getStore()->getId(),
            $amazonCheckoutSessionId
        );
        return $response;
    }

    /**
     * Update Checkout Session to set payment info and transaction metadata
     *
     * @param $quote
     * @param $amazonCheckoutSessionId
     */
    public function updateCheckoutSession($cartId, $amazonCheckoutSessionId)
    {
        // Load quote
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

        $response = $this->amazonAdapter->updateCheckoutSession($quote, $amazonCheckoutSessionId);

        if ($response && isset($response['webCheckoutDetails'])) {
            return $response['webCheckoutDetails']['amazonPayRedirectUrl'];
        }
        return false;
    }
}
