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
namespace Amazon\Maxo\Controller\Payment;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartManagementInterface;
use Amazon\Core\Logger\ExceptionLogger;

/**
 * Class CompleteCheckout
 *
 * @package Amazon\Maxo\Controller\Payment
 */
class CompleteCheckout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\Maxo\CustomerData\CheckoutSession
     */
    private $amazonCheckoutSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * CompleteCheckout constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Amazon\Maxo\CustomerData\CheckoutSession $amazonCheckoutSession
     * @param CartManagementInterface $cartManagement
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $session
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\Maxo\CustomerData\CheckoutSession $amazonCheckoutSession,
        CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $session,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->amazonCheckoutSession = $amazonCheckoutSession;
        $this->cartManagement = $cartManagement;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        $checkoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');

        // Place Order
        if ($checkoutSessionId === $this->amazonCheckoutSession->getCheckoutSessionId()) {
            try {
                if (!$this->session->isLoggedIn()) {
                    $this->checkoutSession->getQuote()->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
                }
                $this->cartManagement->placeOrder($this->checkoutSession->getQuoteId());
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
