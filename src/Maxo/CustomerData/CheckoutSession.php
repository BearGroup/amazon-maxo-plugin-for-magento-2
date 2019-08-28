<?php

namespace Amazon\Maxo\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Amazon Checkout Session section
 */
class CheckoutSession implements SectionSourceInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Amazon\Maxo\Model\CheckoutSessionManagement
     */
    private $checkoutSessionModel;

    /**
     * CheckoutSession constructor.
     * @param \Magento\Framework\Session\Generic $session
     * @param \Amazon\Maxo\Model\CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Amazon\Maxo\Model\CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->session = $session;
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return ['checkoutSessionId' => $this->getCheckoutSessionId()];
    }

    public function getCheckoutSessionId()
    {
        $sess = $this->session->getAmazonCheckoutSessionId();
        if (!$sess) {
            $response = $this->checkoutSessionManagement->createCheckoutSession();
            if ($response) {
                $sess = $response->checkoutSessionId;
                $this->session->setAmazonCheckoutSessionId($sess);
            }
        }
        return $sess;
    }

    public function clearCheckoutSessionId()
    {
        $this->session->unsAmazonCheckoutSessionId();
    }
}
