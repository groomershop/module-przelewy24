define([
    'ko',
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/redirect-on-success',
    'PayPro_Przelewy24/js/model/method-validator',
    'PayPro_Przelewy24/js/model/przelewy24-config'
], function (ko, $, Component, quote, redirectOnSuccessAction, methodValidator, config) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24',
            przelewy24Methods: [],
            selectedPrzelewy24Method: null,
            regulationAccept: false
        },

        initObservable: function () {
            this._super().observe(['przelewy24Methods', 'selectedPrzelewy24Method', 'regulationAccept']);

            quote.paymentMethod.subscribe(function (paymentMethod) {
                if (paymentMethod && paymentMethod.method === this.getCode()) {
                    this.initMethods();
                }
            }.bind(this));

            return this;
        },

        initPreSelected: function () {
            if (this.isChecked() === this.getCode()) {
                this.initMethods();
            }
        },

        getCode: function () {
            return 'przelewy24';
        },

        getLogo: function () {
            return config.przelewy24.logoUrl;
        },

        isChecked: ko.computed(function () {
            if (!quote.paymentMethod()) {
                return null;
            }

            if (!quote.paymentMethod().additional_data || !quote.paymentMethod().additional_data.standalone) {
                return quote.paymentMethod().method;
            }

            return null;
        }),

        getData: function () {
            return {
                method: this.getCode(),
                additional_data: {
                    method: this.selectedPrzelewy24Method() ? this.selectedPrzelewy24Method().id : null,
                    regulation_accept: this.regulationAccept()
                }
            };
        },

        afterPlaceOrder: function () {
            redirectOnSuccessAction.redirectUrl = config.przelewy24.paymentRedirectUrl;
        },

        selectMethod: function (method) {
            this.selectedPrzelewy24Method(method);
        },

        isMethodSelected: function (method) {
            return method === this.selectedPrzelewy24Method();
        },

        isPaymentReady: function () {
            if (this.przelewy24Methods().length > 0 && !this.selectedPrzelewy24Method()) {
                return false;
            }

            return this.isPlaceOrderActionAllowed();
        },

        initMethods: function () {
            if (!this.isSelectPaymentMethodInStoreEnabled()) {
                return;
            }

            if (this.przelewy24Methods().length > 0) {
                return;
            }

            const grandTotal = quote.getTotals()().grand_total;

            let url = config.przelewy24.paymentMethodsUrl;
            if (grandTotal) {
                url += '?amount=' + grandTotal;
            }

            $.ajax({
                url: url,
                dataType: 'json',
                method: 'GET'
            }).done(function (response) {
                this.przelewy24Methods(response);
            }.bind(this));
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        },

        isSelectPaymentMethodInStoreEnabled: function () {
            return config.przelewy24.isSelectPaymentMethodInStoreEnabled;
        },

        isRegulationsAcceptVisible: function () {
            return this.przelewy24Methods().length > 0;
        },

        isMethodPromoted: function (methodId) {
            return config.przelewy24.isERatySCBPromoted && methodId === config.przelewy24.ERatySCBId;
        },

        isMethodVisible: function (method) {
            return (!method.standalone || this.isMethodPromoted(method.id)) && methodValidator.validate(method.id);
        }
    });
});
