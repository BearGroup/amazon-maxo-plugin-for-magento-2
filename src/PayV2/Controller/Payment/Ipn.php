<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

class Ipn extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * @var \Amazon\Payment\Ipn\IpnHandlerFactoryInterface
     */
    private $ipnHandlerFactory;

    /**
     * @var \Amazon\Payment\Api\Ipn\CompositeProcessorInterface
     */
    private $compositeProcessor;

    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Core\Logger\IpnLogger
     */
    private $ipnLogger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\Payment\Ipn\IpnHandlerFactoryInterface $ipnHandlerFactory,
        \Amazon\Payment\Api\Ipn\CompositeProcessorInterface $compositeProcessor,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\Core\Logger\IpnLogger $ipnLogger,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);

        $this->ipnHandlerFactory = $ipnHandlerFactory;
        $this->compositeProcessor = $compositeProcessor;
        $this->amazonConfig = $amazonConfig;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->ipnLogger = $ipnLogger;
    }

    public function execute()
    {
        if (!$this->amazonConfig->isEnabled()) {
            return;
        }

        try {
            $message = $this->_request->getParam('Message');
            $headers = $this->_request->getHeaders()->toArray();
            $body = $this->_request->getContent();

            $this->ipnLogger->info(print_r($headers, 1));
            $this->ipnLogger->info($body);
            $this->ipnLogger->info($message);

            $message = json_decode($message, true);

            if ($message && isset($message['objectId'])) {

            }

        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }
}
