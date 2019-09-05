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

        return Component.extend(
            {
                defaults: {
                    template: 'Amazon_Maxo/checkout-button'
                },
                isVisible: !amazonStorage.isAmazonCheckout(),

                /**
                 * Init
                 */
                initialize: function () {
                    this._super();
                }
            }
        );
    }
);

