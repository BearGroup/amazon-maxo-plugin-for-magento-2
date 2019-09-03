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

define(
    [
        'jquery',
        'ko',
        'Magento_Customer/js/customer-data',
        'Amazon_Maxo/js/model/amazon-maxo-config',
    ],
    function (
        $,
        ko,
        customerData,
        amazonMaxoConfig
    ) {
        'use strict';

        var isEnabled = amazonMaxoConfig.isDefined(),
            isShippingMethodsLoading = ko.observable(false),
            amazonCheckoutInfo = ko.observable(false),
            cacheKey = 'is-amazon-checkout',
            sectionKey = 'amazon-checkout-session';

        return {
            isEnabled: isEnabled,
            isAmazonCheckout: function() {
                var isAmazon = window.location.search.indexOf('amazonCheckoutSessionId') != -1;
                if (isAmazon) {
                    customerData.set(cacheKey, true);
                }
                return customerData.get(cacheKey)();
            },
            clearAmazonCheckout: function() {
                customerData.set(cacheKey, false);
                customerData.set(sectionKey, false);
            },
            getCheckoutSessionId: function() {
                var checkoutSessionData = customerData.get(sectionKey);
                if (checkoutSessionData) {
                    return checkoutSessionData()['checkoutSessionId'];
                }
            },
            reloadCheckoutSessionId: function() {
                customerData.reload([sectionKey]);
            },
            isShippingMethodsLoading: isShippingMethodsLoading,
            amazonCheckoutInfo: amazonCheckoutInfo
        };
    }
);
