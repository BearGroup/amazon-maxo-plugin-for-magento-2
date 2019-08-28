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
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Amazon_Maxo/js/model/amazonMaxoConfig',
    'Amazon_Maxo/js/model/storage',
    'amazonMaxoCheckout'
], function ($, customerData, amazonMaxoConfig, amazonStorage) {
    'use strict';

    if (amazonStorage.isEnabled) {
        $.widget('amazon.AmazonButton', {
            /**
             * Create button
             */
            _create: function () {
                amazon.Pay.renderButton('.amazon-minicart-container-v2', {
                    merchantId: amazonMaxoConfig.getValue('merchantId'),
                    createCheckoutSession: function() {
                        return new Promise(function(resolve, reject) {
                            resolve(amazonStorage.getCheckoutSessionId());
                        });
                    },
                    ledgerCurrency: amazonMaxoConfig.getValue('currency'),
                    checkoutLanguage: amazonMaxoConfig.getValue('language'),
                    productType: 'PayAndShip',
                    placement: amazonMaxoConfig.getValue('placement'),
                    sandbox: amazonMaxoConfig.getValue('sandbox'),
                });
            }
        });

        return $.amazon.AmazonButton;
    }
});
