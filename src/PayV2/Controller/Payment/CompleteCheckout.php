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
namespace Amazon\PayV2\Controller\Payment;

use Magento\Framework\App\ObjectManager;
use Amazon\Core\Logger\ExceptionLogger;

/**
 * Class CompleteCheckout
 *
 * @package Amazon\PayV2\Controller\Payment
 */
class CompleteCheckout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\PayV2\CustomerData\CheckoutSession
     */
    private $amazonCheckoutSession;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    private $onepage;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\PayV2\CustomerData\CheckoutSession $amazonCheckoutSession,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->amazonCheckoutSession = $amazonCheckoutSession;
        $this->onepage = $onepage;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        $checkoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');

        // Save Order
        if ($checkoutSessionId === $this->amazonCheckoutSession->getCheckoutSessionId()) {
            try {
                $this->onepage->saveOrder();
                $this->amazonCheckoutSession->clearCheckoutSessionId();
                return $this->_redirect('checkout/onepage/success');
            } catch (\Exception $e) {
                $this->exceptionLogger->logException($e);
                $this->amazonCheckoutSession->clearCheckoutSessionId();
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage('Invalid amazonCheckoutSessionId');
        }

        return $this->_redirect('checkout/cart');
    }
}
