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
    'Amazon_Maxo/js/model/amazon-maxo-config',
    'Amazon_Maxo/js/model/storage',
    'mage/url',
    'amazonMaxoCheckout',
], function ($, customerData, amazonMaxoConfig, amazonStorage, url) {
    'use strict';

    if (amazonStorage.isEnabled) {
        $.widget('amazon.AmazonButton', {
            options: {
                placement: amazonMaxoConfig.getValue('placement'),
                selector: '.amazon-checkout-button'
            },

            /**
             * Create button
             */
            _create: function () {
                if (!amazonStorage.getCheckoutSessionId()) {
                    amazonStorage.reloadCheckoutSessionId();
                }

                amazon.Pay.renderButton(this.options.selector, {
                    merchantId: amazonMaxoConfig.getValue('merchantId'),
                    createCheckoutSession: {
                        url: url.build('amazon_maxo/checkout/createSession'),
                        method: 'PUT',
                        extractAmazonCheckoutSessionId: function(response) {
                            return amazonStorage.getCheckoutSessionId();
                        }
                    },
                    ledgerCurrency: amazonMaxoConfig.getValue('currency'),
                    checkoutLanguage: amazonMaxoConfig.getValue('language'),
                    placement: this.options.placement,
                    sandbox: amazonMaxoConfig.getValue('sandbox'),
                });
                $('.amazon-button-container-v2 .field-tooltip').fadeIn();
            }
        });

        return $.amazon.AmazonButton;
    }
});
