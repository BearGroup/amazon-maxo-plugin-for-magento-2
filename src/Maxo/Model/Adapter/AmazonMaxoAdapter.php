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

namespace Amazon\Maxo\Model\Adapter;

class AmazonMaxoAdapter
{

    public function __construct(
        \Amazon\Maxo\Client\ClientFactoryInterface $clientFactory,
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        \Amazon\Core\Helper\Data $amazonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->amazonConfig = $amazonConfig;
        $this->amazonHelper = $amazonHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function createCheckoutSession($storeId)
    {
        $payload = [
            'webCheckoutDetails' => [
                'checkoutReviewReturnUrl' => $this->amazonConfig->getCheckoutReviewReturnUrl(),
            ],
            'storeId' => $this->amazonConfig->getClientId(),
        ];

        $headers = [
            'x-amz-pay-idempotency-key' => uniqid(),
        ];

        $response = $this->clientFactory->create($storeId)->createCheckoutSession($payload, $headers);

        if (!$response || (isset($response['response']) && strpos($response['response'],'checkoutSessionId') === false)) {
            $this->logger->debug(__('Unable to create checkout session'));
        } else {
            return json_decode($response['response']);
        }
    }

    public function getCheckoutSession($storeId, $checkoutSessionId)
    {
        //return json_decode('{"checkoutSessionId":"2b577683-babc-459f-ae08-0f75f9c59bfb","webCheckoutDetails":{"checkoutReviewReturnUrl":"https://ap.stage.beargroup.com/checkout/","checkoutResultReturnUrl":null,"amazonPayRedirectUrl":null},"productType":"PayAndShip","paymentDetails":{"paymentIntent":null,"canHandlePendingAuthorization":false,"chargeAmount":null,"softDescriptor":null},"merchantMetadata":{"merchantReferenceId":null,"merchantStoreName":null,"noteToBuyer":null,"customInformation":null},"supplementaryData":null,"buyer":{"name":"Jonah Ellison","email":"jonah@beargroup.com","buyerId":"amzn1.account.A07479412FGBCH8846CXM"},"paymentPreferences":[{"billingAddress":null,"paymentDescriptor":"AmazonPay"}],"statusDetails":{"state":"Open","reasonCode":null,"reasonDescription":null,"lastUpdatedTimestamp":"20190822T024530Z"},"shippingAddress":{"name":"Jack Smith","addressLine1":"83034 Terry Ave","addressLine2":null,"addressLine3":null,"city":"Seattle","county":null,"district":null,"stateOrRegion":"WA","postalCode":"98121","countryCode":"US"},"platformId":null,"chargePermissionId":null,"chargeId":null,"constraints":[{"constraintId":"ChargeAmountNotSet","description":"chargeAmount is not set."},{"constraintId":"CheckoutResultReturnUrlNotSet","description":"checkoutResultReturnUrl is not set."},{"constraintId":"PaymentIntentNotSet","description":"paymentIntent is not set."}],"creationTimestamp":"20190822T024512Z","expirationTimestamp":"20190823T024512Z","storeId":"amzn1.application-oa2-client.6f774de2482149b18e5df3796d0aef0f","providerMetadata":{"providerReferenceId":null},"releaseEnvironment":"Sandbox"}');

        $response = $this->clientFactory->create($storeId)->getCheckoutSession($checkoutSessionId);

        if (!$response || (isset($response['response']) && strpos($response['response'],'checkoutSessionId') === false)) {
            $this->logger->debug(__('Unable to get checkout session'));
        } else {
            return json_decode($response['response']);
        }
    }

    /**
     * Update Checkout Session to set payment info and transaction metadata
     *
     * @param $quote
     * @param $checkoutSessionId
     * @return mixed
     */
    public function updateCheckoutSession($quote, $checkoutSessionId)
    {
        $storeId = $quote->getStoreId();
        $store = $quote->getStore();

        $payload = [
            'webCheckoutDetails' => [
                'checkoutResultReturnUrl' => $store->getUrl('amazon_maxo/payment/completeCheckout', ['_forced_secure' => true])
            ],
            'paymentDetails' => [
                'paymentIntent' => 'Authorize',
                'canHandlePendingAuthorization' => true,
                'chargeAmount' => [
                    'amount' => number_format($quote->getGrandTotal(), 2),
                    'currencyCode' => $store->getCurrentCurrency()->getCode(),
                ],
            ],
            'merchantMetadata' => [
                'merchantReferenceId' => $this->amazonConfig->getMerchantId(),
                'merchantStoreName' => $this->amazonHelper->getStoreName() ?? $store->getName(),
                //noteToBuyer => '',
                //customInformation => '',
            ]
        ];

        $response = $this->clientFactory->create($storeId)->updateCheckoutSession($checkoutSessionId, $payload);

        if (!$response || (isset($response['response']) && strpos($response['response'],'checkoutSessionId') === false)) {
            $this->logger->debug(__('Unable to update checkout session'));
        } else {
            return json_decode($response['response']);
        }
    }



}
