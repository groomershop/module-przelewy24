define([
    'jquery',
    'PayPro_Przelewy24/js/view/payment/bundle-pay',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/model/quote',
    'PayPro_Przelewy24/js/model/google-pay',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, Component, config, quote, GooglePay, modal, $t) {
    'use strict';

    const P24TargetElementId = 'p24-google-pay-modal';
    const interactionModalSelector = '#' + P24TargetElementId;

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-google-pay',
            buttonContainerId: 'google-pay-container'
        },
        isInitialized: false,

        initialize: function () {
            this._super();

            quote.paymentMethod.subscribe(function () {
                this.initGooglePay();
            }.bind(this));

            return this;
        },

        getCode: function () {
            return 'przelewy24_google_pay';
        },

        getLogo: function () {
            return config.przelewy24GooglePay.logoUrl;
        },

        getData: function () {
            return {
                method: this.getCode(),
                additional_data: {
                    sessionId: this.sessionId()
                }
            };
        },

        initGooglePay: function () {
            if (this.isChecked() !== this.getCode()) {
                return;
            }

            if (this.isInitialized) {
                return;
            }

            this.isInitialized = true;

            this.createScript({
                id: 'przelewy24-google-pay',
                src: 'https://pay.google.com/gp/p/js/pay.js',
                callback: this.initializePaymentsClient.bind(this)
            });
        },

        initBundlePay: function () {
            this.createScript({
                id: 'przelewy24-bundle-google-pay',
                src: config.przelewy24GooglePay.scriptUrl.replace('%s', this.token())
            });

            document.addEventListener('Przelewy24CardWhileLabelHandlerReady', () => {
                Przelewy24CardWhileLabelHandler.config({
                    P24TargetElementId,
                    P24ScriptFailedEventCallback: this.paymentError.bind(this),
                    P24NeedInteractionEventCallback: () => {
                        $(interactionModalSelector).modal('openModal');
                        this.stopLoader();
                    },
                    P24ScriptSuccessfulEndsEventCallback: () => {
                        $(interactionModalSelector).modal('closeModal');
                        this.paymentDeferredObject.resolve();
                    }
                });
                this.startLoader();
                Przelewy24CardWhileLabelHandler.main();
            });
        },

        initializePaymentsClient: function () {
            modal(config.przelewy24InteractionModalConfig, $(interactionModalSelector));
            const paymentsClient = GooglePay.getPaymentsClient();
            paymentsClient.isReadyToPay(GooglePay.isReadyToPayRequest()).then(function (response) {
                if (response.result) {
                    GooglePay.createButton(this.onGooglePaySuccess.bind(this), this.buttonContainerId);

                    return;
                }

                this.messageContainer.addErrorMessage({
                    message: $t('Unable to pay using Google Pay.')
                });
            }.bind(this)).catch(function (err) {
                console.error(err);
            });
        },

        onGooglePaySuccess: function (paymentData) {
            this.registerBundleTransaction(
                config.przelewy24GooglePay.registerTransactionUrl,
                paymentData.paymentMethodData.tokenizationData.token
            );
        }
    });
});
