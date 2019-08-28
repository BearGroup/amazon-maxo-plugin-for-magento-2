define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'amazon_payment_v2',
                component: 'Amazon_Maxo/js/view/payment/method-renderer/amazon-payment-method'
            }
        );
        return Component.extend({});
    }
);