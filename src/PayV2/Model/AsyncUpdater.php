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

namespace Amazon\PayV2\Model;

class AsyncUpdater
{
    /**
     * @var AsyncManagement\AuthorizationFactory
     */
    private $authorizationFactory;

    /**
     * @var \Amazon\PayV2\Api\Data\AsyncInterfaceFactory
     */
    private $asyncFactory;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    private $adminNotifier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AsyncUpdater constructor.
     * @param AsyncManagement\AuthorizationFactory $authorizationFactory
     * @param \Amazon\PayV2\Api\Data\AsyncInterfaceFactory $asyncFactory
     * @param \Magento\Framework\Notification\NotifierInterface $adminNotifier
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Amazon\PayV2\Model\AsyncManagement\AuthorizationFactory $authorizationFactory,
        \Amazon\PayV2\Api\Data\AsyncInterfaceFactory $asyncFactory,
        \Magento\Framework\Notification\NotifierInterface $adminNotifier,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->authorizationFactory = $authorizationFactory;
        $this->asyncFactory = $asyncFactory;
        $this->adminNotifier = $adminNotifier;
        $this->logger = $logger;
    }

    /**
     * @param Async $async
     */
    public function processPending($async)
    {
        try {
            $async->getResource()->beginTransaction();
            $async->setLockOnLoad(true);

            switch ($async->getPendingAction()) {
                case AsyncManagement::ACTION_AUTH:
                    $this->authorizationFactory->create()->processPendingAuthorization($async->getPendingId());
                    $this->completePending($async);
                    break;
                case AsyncManagement::ACTION_REFUND:
                    // @todo verify refund
                    $this->completePending($async);
                    break;
            }

            $async->getResource()->commit();
        } catch (\Exception $e) {
            $this->logger->error($e);
            $async->getResource()->rollBack();
        }
    }

    /**
     * Complete successful async pending action
     *
     * @param Async $async
     */
    protected function completePending($async)
    {
        $async->setIsPending(false)->save();
    }
}
