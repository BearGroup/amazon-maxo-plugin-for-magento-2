define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Amazon_Maxo/js/action/place-order'
    ],
    function (
        $,
        Component,
        placeOrderAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_Maxo/payment/amazon-payment'
            },

            initObservable: function () {
                this._super();
                this.selectPaymentMethod();
                return this;
            },

            /**
             * Save order
             */
            placeOrder: function (data, event) {
                var placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate()) {
                    //this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData());
                }

                return false;
            }

        });
    }
);