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
namespace Amazon\Maxo\Model\AsyncManagement;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

abstract class AbstractOperation
{
    protected function setOnHold(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_HOLDED);
    }

    protected function setProcessing(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_PROCESSING);
    }

    protected function setPaymentReview(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_PAYMENT_REVIEW);
    }

    protected function setOrderState(OrderInterface $order, $state)
    {
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->setState($state)->setStatus($status);
    }
}
