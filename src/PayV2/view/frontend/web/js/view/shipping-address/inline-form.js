define([
    'uiComponent',
    'ko',
    'Amazon_PayV2/js/model/storage'
], function (Component, ko, amazonStorage) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/shipping-address/inline-form',
            formSelector: 'co-shipping-form'
        },

        /**
         * Init inline form
         */
        initObservable: function () {
            this._super();
            return this;
        },

        /**
         * Show/hide inline form depending on Amazon checkout status
         */
        manipulateInlineForm: function () {
            this.hideInlineForm(amazonStorage.isAmazonCheckout());
        },

        /**
         * Show/hide inline form
         */
        hideInlineForm: function(hide) {
            var elem = document.getElementById(this.formSelector);

            if (elem) {
                document.getElementById(this.formSelector).style.display = hide ? 'none' : 'block';
            }
        }
    });
});
