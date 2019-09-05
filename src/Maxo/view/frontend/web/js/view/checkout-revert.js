/*global define*/
define(
    [
        'uiComponent',
        'Amazon_Maxo/js/model/storage'
    ],
    function (
        Component,
        amazonStorage
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_Maxo/checkout-revert'
            },
            isAmazonCheckout: amazonStorage.isAmazonCheckout(),

            /**
             * Init
             */
            initialize: function () {
                this._super();
            },

            /**
             * Revert checkout
             */
            revertCheckout: function () {
                amazonStorage.revertCheckout();
                window.location.replace(window.checkoutConfig.checkoutUrl);
            }
        });
    }
);
