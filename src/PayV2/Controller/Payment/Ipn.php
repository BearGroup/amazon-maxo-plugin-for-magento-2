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

use Amazon\Core\Logger\ExceptionLogger;
use Magento\Framework\App\ObjectManager;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

/**
 * Class Ipn
 *
 * IPN endpoint for Amazon Simple Notification Service
 * @link https://docs.aws.amazon.com/sns/latest/dg/sns-http-https-endpoint-as-subscriber.html
 */
class Ipn extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\Core\Logger\IpnLogger
     */
    private $ipnLogger;

    /**
     * @var \Amazon\PayV2\Model\AsyncManagement\ChargeFactory
     */
    private $chargeFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\PayV2\Model\AsyncManagement\ChargeFactory $chargeFactory,
        \Amazon\Core\Logger\IpnLogger $ipnLogger,
        ExceptionLogger $exceptionLogger = null
    ) {
        // Bypass Magento's CsrfValidator (which rejects POST) and use Amazon SNS Message Validator instead
        $context->getRequest()->setMethod('PUT');
        parent::__construct($context);

        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->ipnLogger = $ipnLogger;
        $this->chargeFactory = $chargeFactory;
    }

    public function execute()
    {
        try {
            //*
            // Log server IPN requests for localdev testing (e.g. with Postman)
            $this->ipnLogger->info(print_r($this->getRequest()->getHeaders()->toArray(), 1));
            $this->ipnLogger->info($this->getRequest()->getContent());
            //*/

            // Amazon SNS Message Validator
            $snsMessage = Message::fromRawPostData();
            $validator = new MessageValidator();

            // Message Validator checks SigningCertURL, SignatureVersion, and Signature
            if ($validator->isValid($snsMessage)) {
                $message = json_decode($snsMessage['Message'], true);

                // Process message
                if (isset($message['ObjectType'])) {
                    switch ($message['ObjectType']) {
                        case 'CHARGE':
                            $this->chargeFactory->create()->processStateChange($message['ObjectId']);
                            break;
                        case 'REFUND':
                            // @todo verify refund
                            break;
                    }
                }
            }

        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            $this->ipnLogger->debug(($e->getMessage()));
            throw $e;
        }
    }
}
