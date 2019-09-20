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

namespace Amazon\PayV2\Model\AsyncManagement;

use Magento\Sales\Api\Data\TransactionInterface as Transaction;

class Authorization extends AbstractOperation
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter
     */
    private $amazonAdapter;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    private $transactionBuilder;

    /**
     * Authorization constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        parent::__construct($orderRepository, $transactionRepository, $searchCriteriaBuilder);
        $this->orderRepository = $orderRepository;
        $this->amazonAdapter = $amazonAdapter;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * Create authorization charge for pending authorization
     *
     * @param string $transactionId (chargePermissionId)
     */
    public function processPendingAuthorization($transactionId)
    {
        $transaction = $this->getTransaction($transactionId, Transaction::TYPE_AUTH);

        if ($transaction) {
            $order = $this->orderRepository->get($transaction->getOrderId());

            $response = $this->amazonAdapter->createCharge(
                $order->getStoreId(),
                $transactionId,
                $order->getGrandTotal(),
                $order->getOrderCurrencyCode()
            );

            if (!empty($response['chargeId'])) {
                $payment = $order->getPayment();

                // Create new authorization transaction and set as parent transaction for payment
                $parentTransaction = $this->transactionBuilder->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($response['chargeId'])
                    ->setFailSafe(true)
                    ->build(Transaction::TYPE_AUTH);

                $formattedAmount = $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized());
                $message = __('Authorized amount of %1.', $formattedAmount);
                $payment->addTransactionCommentsToOrder($parentTransaction, $message);
                $payment->setIsTransactionClosed(false);
                $payment->setParentTransactionId($response['chargeId']);

                $parentTransaction
                    ->setIsClosed(false)
                    ->setParentId($transaction->getTransactionId())
                    ->setParentTxnId($transactionId)
                ;

                $this->setProcessing($order);
                $order->save();

                // Close pending authorization transaction
                $transaction
                    ->setIsClosed(true)
                    ->save();

            } else {
                throw new \Exception($response['reasonCode'] . ' ' . $response['message']);
            }
        }
    }

}
