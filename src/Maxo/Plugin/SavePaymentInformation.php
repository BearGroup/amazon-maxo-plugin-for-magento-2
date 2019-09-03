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

use Magento\Quote\Api\PaymentMethodManagementInterface;
use Amazon\Maxo\Gateway\Config\Config as GatewayConfig;
use Magento\Quote\Api\CartRepositoryInterface;

class SavePaymentInformation
{
    /**
     * @var \Amazon\Maxo\Model\CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * @var \Amazon\Maxo\CustomerData\CheckoutSession
     */
    private $amazonCheckoutSession;

    /**
     * SavePaymentInformation constructor.
     * @param \Amazon\Maxo\Model\CheckoutSessionManagement $checkoutSessionManagement
     * @param \Amazon\Maxo\CustomerData\CheckoutSession $amazonCheckoutSession
     */
    public function __construct(
        \Amazon\Maxo\Model\CheckoutSessionManagement $checkoutSessionManagement,
        \Amazon\Maxo\CustomerData\CheckoutSession $amazonCheckoutSession
    ) {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->amazonCheckoutSession = $amazonCheckoutSession;
    }

    /**
     * @param PaymentMethodManagementInterface $subject
     * @param $result
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSavePaymentInformation(
        $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress,
        $result
    ) {
        if ($paymentMethod->getMethod() == GatewayConfig::CODE) {

            return $this->checkoutSessionManagement->updateCheckoutSession(
                $cartId,
                $this->amazonCheckoutSession->getCheckoutSessionId()
            );

        }
        return $result;
    }
}
