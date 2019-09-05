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
namespace Amazon\Maxo\Cron;

use Amazon\Maxo\Api\Data\AsyncInterface;
use Amazon\Core\Model\Config\Source\UpdateMechanism;
use Magento\Framework\Data\Collection;

class ProcessAsync
{
    /**
     * @var \Amazon\Maxo\Model\ResourceModel\Async\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amazon\Maxo\Model\AsyncUpdaterFactory
     */
    private $asyncUpdater;

    /**
     * @var \Amazon\Core\Helper\Data
     */
    private $coreHelper;

    /**
     * @var \Amazon\Maxo\Model\ResourceModel\Async\CollectionFactory
     */
    private $asyncCollectionFactory;

    /**
     * @var int
     */
    private $limit;

    public function __construct(
        \Amazon\Maxo\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory,
        \Amazon\Maxo\Model\AsyncUpdater $asyncUpdater,
        \Amazon\Core\Helper\Data $coreHelper,
        $limit = 100
    ) {
        $limit = (int)$limit;

        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be greater than 0.');
        }

        $this->asyncCollectionFactory = $asyncCollectionFactory;
        $this->asyncUpdater = $asyncUpdater;
        $this->coreHelper = $coreHelper;
        $this->limit = $limit;
    }

    public function execute()
    {
        if (UpdateMechanism::IPN === $this->coreHelper->getUpdateMechanism()) {
            return;
        }

        $collection = $this->asyncCollectionFactory
            ->create()
            ->addFilter(AsyncInterface::IS_PENDING, true)
            ->addOrder(AsyncInterface::ID, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        /** @var \Amazon\Maxo\Model\Async $async */
        foreach ($collection as $async) {
            $this->asyncUpdater->processPending($async);
        }
    }
}
