define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function ($, Component, config, placeOrderAction, quote, fullScreenLoader, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            token: null,
            sessionId: null
        },

        paymentDeferredObject: null,

        initObservable: function () {
            this._super().observe(['token', 'sessionId']);

            return this;
        },

        getPlaceOrderDeferredObject: function () {
            this.paymentDeferredObject = $.Deferred();

            $.when(placeOrderAction(this.getData(), this.messageContainer)).then(function () {
                this.initBundlePay();
            }.bind(this));

            return $.when(this.paymentDeferredObject);
        },

        createScript: function (config) {
            const oldScript = document.getElementById(config.id);
            if (oldScript) {
                oldScript.outerHTML = '';
            }

            const script = document.createElement('script');
            script.setAttribute('id', config.id);
            script.setAttribute('src', config.src);
            document.body.appendChild(script);
            if (config.callback) {
                this.startLoader();
                script.onload = function() {
                    config.callback();
                    this.stopLoader();
                }.bind(this);
            }
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        },

        initBundlePay: function () {
            // Bundle pay implementation
        },

        registerBundleTransaction: function (url, tokenObject) {
            $.ajax({
                url: url,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    cartId: quote.getQuoteId(),
                    tokenObject: tokenObject
                }),
                showLoader: true,
                method: 'POST'
            }).done(function (response) {
                this.token(response.token);
                this.sessionId(response.transaction);
                this.placeOrder();
            }.bind(this)).fail(function (response) {
                if (response.responseJSON && response.responseJSON.message) {
                    this.messageContainer.addErrorMessage({
                        message: response.responseJSON.message
                    });

                    return;
                }

                this.messageContainer.addErrorMessage({
                    message: $t('Please refresh page and try again.')
                });
            }.bind(this));
        },

        paymentError: function () {
            this.stopLoader();
            this.paymentDeferredObject.reject();
            this.messageContainer.addErrorMessage({
                message: $t('Transaction has been declined. Please refresh page and try again.')
            });
        },

        startLoader: function () {
            fullScreenLoader.startLoader();
        },

        stopLoader: function () {
            fullScreenLoader.stopLoader();
        }
    });
});
