/*global define*/

define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/action/set-shipping-information',
        'Amazon_PayV2/js/model/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/address-converter',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/model/address-list',
        'uiRegistry'
    ],
    function (
        $,
        Component,
        ko,
        customer,
        quote,
        selectShippingAddress,
        shippingProcessor,
        setShippingInformationAction,
        amazonStorage,
        shippingService,
        addressConverter,
        storage,
        fullScreenLoader,
        errorProcessor,
        urlBuilder,
        checkoutData,
        checkoutDataResolver,
        addressList,
        registry
    ) {
        'use strict';

        var self;

        require(['amazonPayV2Checkout']);

        return Component.extend({
            defaults: {
                template: 'Amazon_PayV2/checkout-address'
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonCheckout: amazonStorage.isAmazonCheckout(),
            //isAmazonEnabled: ko.observable(registry.get('amazonPayment').isPwaEnabled),
            rates: shippingService.getShippingRates(),

            /**
             * Init
             */
            initialize: function () {
                self = this;
                this._super();
                if (this.isAmazonCheckout) {
                    this.getShippingAddressFromAmazon();
                }
            },

            /**
             * Call when component template is rendered
             */
            initAddress: function () {
                var addressDataList = $.extend({}, quote.shippingAddress());

                // Only display one address from Amazon
                addressList.removeAll();

                // Remove empty street array values for list view
                if ($.isArray(addressDataList.street)) {
                    addressDataList.street = addressDataList.street.filter(function (el) {
                        return el != null;
                    });
                }
                addressDataList.telephone = '';
                addressList.push(addressDataList);
                this.setEmail(addressDataList.email);
            },

            /**
             * Get shipping address from Amazon API
             */
            getShippingAddressFromAmazon: function () {
                var serviceUrl, payload;

                // Only display one address from Amazon
                addressList.removeAll();

                amazonStorage.isShippingMethodsLoading(true);
                shippingService.isLoading(true);
                serviceUrl = urlBuilder.createUrl('/amazon-v2-shipping-address/:amazonCheckoutSessionId', {
                    amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId()
                }),

                storage.put(
                    serviceUrl
                ).done(
                    function (data) {

                        // Invalid checkout session
                        if (!data.length) {
                            //self.resetCheckout();
                            return;
                        }

                        var amazonAddress = data.shift(),
                            addressData = addressConverter.formAddressDataToQuoteAddress(amazonAddress),
                            i;

                        console.log(amazonAddress);
                        console.log(addressData);

                        self.setEmail(amazonAddress.email);

                        // If telephone is blank set it to 00000000 so it passes the required validation
                        addressData.telephone = !addressData.telephone ? '0000000000' : addressData.telephone;

                        // Fill in blank street fields
                        if ($.isArray(addressData.street)) {
                            for (i = addressData.street.length; i <= 2; i++) {
                                addressData.street[i] = '';
                            }
                        }
                        checkoutData.setShippingAddressFromData(
                            addressConverter.quoteAddressToFormAddressData(addressData)
                        );
                        checkoutDataResolver.resolveEstimationAddress();

                        self.initAddress();

                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        //remove shipping loader and set shipping rates to 0 on a fail
                        shippingService.setShippingRates([]);
                        amazonStorage.isShippingMethodsLoading(false);
                    }
                );
            },
            setEmail: function(email) {
                $('#customer-email').val(email);
                checkoutData.setInputFieldEmailValue(email);
                checkoutData.setValidatedEmailValue(email);
                quote.guestEmail = email;
            },
            resetCheckout: function() {
                amazonStorage.clearAmazonCheckout();
                window.location =  window.checkoutConfig.checkoutUrl;
            }
        });
    }
);
