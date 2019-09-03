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

namespace Amazon\Maxo\Gateway\Response;

use Amazon\Maxo\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CompleteAuthHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransactionIdHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Payment $orderPayment */
            $orderPayment = $paymentDO->getPayment();
            $order = $this->subjectReader->getOrder();

            // Successful Authorization
            if (!empty($response['chargeId'])) {
                $orderPayment->setTransactionId($response['chargeId']);
            } else { // Pending Authorization
                $orderPayment->setIsTransactionPending(true);
                $orderPayment->setTransactionId($response['chargePermissionId']);
                $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)->setStatus(
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
                );
                /* @todo
                $this->pendingAuthorizationFactory->create()
                    ->setAuthorizationId($response['chargePermissionId'])
                    ->save();
                 */
            }

            $orderPayment->setIsTransactionClosed(false);

        }
    }
}
