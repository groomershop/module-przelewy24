define([
    'jquery',
    'PayPro_Przelewy24/js/view/payment/bundle-pay',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'PayPro_Przelewy24/js/model/apple-pay',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, Component, config, quote, additionalValidators, ApplePay, modal, $t) {
    'use strict';

    const P24TargetElementId = 'p24-apple-pay-modal';
    const interactionModalSelector = '#' + P24TargetElementId;

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-apple-pay'
        },

        getCode: function () {
            return 'przelewy24_apple_pay';
        },

        getLogo: function () {
            return config.przelewy24ApplePay.logoUrl;
        },

        getData: function () {
            return {
                method: this.getCode(),
                additional_data: {
                    sessionId: this.sessionId()
                }
            };
        },

        initApplePay: function () {
            if (!additionalValidators.validate()) {
                return;
            }

            modal(config.przelewy24InteractionModalConfig, $(interactionModalSelector));

            try {
                this.session = new ApplePaySession(1, ApplePay.getPaymentRequest());
                this.session.onvalidatemerchant = function (event) {
                    if (!event.validationURL) {
                        this.session.completePayment(ApplePaySession.STATUS_FAILURE);
                        this.session.abort();
                        throw new Error('Session validation URL not found');
                    }

                    const validationURL = event.validationURL;

                    $.ajax({
                        url: '/przelewy24/payment/applePaySession',
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            validationUrl: validationURL
                        }),
                        showLoader: true,
                        method: 'POST'
                    }).done(function (response) {
                        this.session.completeMerchantValidation(response);
                    }.bind(this)).fail(function () {
                        this.session.completePayment(ApplePaySession.STATUS_FAILURE);
                        this.session.abort();
                        this.messageContainer.addErrorMessage({
                            message: $t('Unable to initialize ApplePaySession')
                        });
                    }.bind(this));
                }.bind(this);

                this.session.onpaymentauthorized = this.onApplePaySuccess.bind(this);

                this.session.begin();
            } catch (err) {
                this.messageContainer.addErrorMessage({
                    message: $t('Unable to initialize ApplePaySession')
                });
            }
        },

        initBundlePay: function () {
            this.createScript({
                id: 'przelewy24-bundle-apple-pay',
                src: config.przelewy24ApplePay.scriptUrl.replace('%s', this.token())
            });

            document.addEventListener('Przelewy24CardWhileLabelHandlerReady', () => {
                Przelewy24CardWhileLabelHandler.config({
                    P24TargetElementId,
                    P24ScriptFailedEventCallback: this.paymentError.bind(this),
                    P24NeedInteractionEventCallback: () => {
                        this.stopLoader();
                    },
                    P24ScriptSuccessfulEndsEventCallback: () => {
                        this.paymentDeferredObject.resolve();
                    }
                });
                this.startLoader();
                Przelewy24CardWhileLabelHandler.main();
                this.session.completePayment(ApplePaySession.STATUS_SUCCESS);
            });
        },

        onApplePaySuccess: function (event) {
            this.registerBundleTransaction(
                config.przelewy24ApplePay.registerTransactionUrl,
                JSON.stringify(event.payment.token.paymentData)
            );
        }
    });
});
