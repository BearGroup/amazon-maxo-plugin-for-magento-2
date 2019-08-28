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
namespace Amazon\Maxo\Plugin;

class CheckoutProcessor
{
    /**
     * @var \Amazon\Maxo\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var  \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * CheckoutProcessor constructor.
     * @param \Amazon\Maxo\Model\AmazonConfig $amazonConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Checkout LayoutProcessor after process plugin.
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
    {
        $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress'];
        $paymentConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment'];

        if ($this->amazonConfig->isEnabled()) {
            $shippingConfig['component'] = 'Amazon_Maxo/js/view/shipping';
            $shippingConfig['children']['customer-email']['component'] = 'Amazon_Maxo/js/view/form/element/email';
            $shippingConfig['children']['address-list']['component'] = 'Amazon_Maxo/js/view/shipping-address/list';
            $shippingConfig['children']['address-list']['rendererTemplates']['new-customer-address']
            ['component'] = 'Amazon_Maxo/js/view/shipping-address/address-renderer/default';

            $shippingConfig['children']['shipping-address-fieldset']['children']
            ['inline-form-manipulator']['component'] = 'Amazon_Maxo/js/view/shipping-address/inline-form';

            $paymentConfig['children']['payments-list']['component'] = 'Amazon_Maxo/js/view/payment/list';
        } else {
            unset($shippingConfig['children']['customer-email']['children']['amazon-button-region']);
            unset($shippingConfig['children']['before-form']['children']['amazon-maxo-address']);
            unset($paymentConfig['children']['renders']['children']['amazon_payment']);
            unset($paymentConfig['children']['payments-list']['children']['amazon_payment-form']);
        }

        return $jsLayout;
    }
}
