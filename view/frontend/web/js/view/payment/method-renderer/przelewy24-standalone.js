define([
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/redirect-on-success',
    'PayPro_Przelewy24/js/model/przelewy24-config'
], function (ko, Component, quote, redirectOnSuccessAction, config) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-standalone',
            methodId: null,
            regulationAccept: false
        },

        initObservable: function () {
            this._super().observe(['regulationAccept']);

            return this;
        },

        getCode: function () {
            return 'przelewy24_' + this.methodId;
        },

        getLogo: function () {
            return this.logoUrl;
        },

        isChecked: ko.computed(function () {
            if (!quote.paymentMethod()) {
                return null;
            }

            if (!quote.paymentMethod().additional_data || !quote.paymentMethod().additional_data.method) {
                return null;
            }

            return quote.paymentMethod().method + '_' + quote.paymentMethod().additional_data.method;
        }),

        getData: function () {
            return {
                method: 'przelewy24',
                additional_data: {
                    method: this.methodId,
                    standalone: true,
                    regulation_accept: this.regulationAccept()
                }
            };
        },

        afterPlaceOrder: function () {
            redirectOnSuccessAction.redirectUrl = config.przelewy24.paymentRedirectUrl;
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        }
    });
});
