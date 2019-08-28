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
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\Maxo\Api\CheckoutSessionManagementInterface $checkoutSessionManagement,
        \Amazon\Maxo\CustomerData\CheckoutSession $amazonCheckoutSession,
        //\Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        CartManagementInterface $cartManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCartManagement,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->amazonCheckoutSession = $amazonCheckoutSession;
        $this->amazonConfig = $amazonConfig;
        $this->cartManagement = $cartManagement;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->messageManager = $messageManager;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        $checkoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');
        //$checkoutSessionId = $this->amazonCheckoutSession->getCheckoutSessionId();
        if ($checkoutSessionId) {
            $this->checkoutSessionManagement->completeCheckout($checkoutSessionId);
            $this->amazonCheckoutSession->clearCheckoutSessionId();
        }

        try {
            if (!$this->session->isLoggedIn()) {
                $this->checkoutSession->getQuote()->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
            }
            $this->cartManagement->placeOrder($this->checkoutSession->getQuoteId());
            return $this->_redirect('checkout/onepage/success');
        } catch(\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }
}
