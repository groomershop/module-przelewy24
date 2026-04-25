define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/model/quote',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader',
    'PayPro_Przelewy24/js/utils/script-loader',
    'PayPro_Przelewy24/js/utils/iframe-remove',
    'mage/translate'
], function ($, Component, quote, config, modal, loader, loadScript, iframeRemove, $t) {
    'use strict';

    const P24TargetElementId = 'p24-cc-vault-modal';
    const interactionModalSelector = '#' + P24TargetElementId;

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-card-vault',
            sessionId: null,
        },

        initObservable: function () {
            this._super().observe(['sessionId']);

            return this;
        },

        getData: function () {
            return {
                method: this.getCode(),
                additional_data: {
                    sessionId: this.sessionId(),
                    public_hash: this.getToken(),
                }
            };
        },

        getToken: function () {
            return this.publicHash;
        },

        getCode: function () {
            return 'przelewy24_card_vault';
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        },

        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        getCardType: function () {
            return this.details.type;
        },

        registerTransaction: function () {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: config.przelewy24Card.registerVaultTransactionUrl,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({ cartId: quote.getQuoteId(), hash: this.getToken() }),
                    showLoader: true,
                    method: 'POST',
                    success: function(response) {
                        resolve(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        reject({ jqXHR, textStatus, errorThrown });
                    }
                });
            });
        },

        submitPaymentAndPlaceOrder: function () {
            loader.startLoader();
            modal(config.przelewy24InteractionModalConfig, $(interactionModalSelector));
            this.registerTransaction().then((response) => {
                this.sessionId(response.transaction);
                document.addEventListener('Przelewy24CardWhileLabelHandlerReady', () => {
                    Przelewy24CardWhileLabelHandler.config({
                        P24TargetElementId,
                        P24ScriptFailedEventCallback: () => {
                            loader.stopLoader();
                            this.messageContainer.addErrorMessage({
                                message: $t('Transaction has been declined. Please refresh page and try again.')
                            });
                            iframeRemove(P24TargetElementId);
                        },
                        P24NeedInteractionEventCallback: () => {
                            $(interactionModalSelector).modal('openModal');
                            loader.stopLoader();
                        },
                        P24ScriptSuccessfulEndsEventCallback: () => {
                            loader.startLoader();
                            $(interactionModalSelector).modal('closeModal');
                            iframeRemove(P24TargetElementId);
                            this.placeOrder();
                        }
                    });
                    Przelewy24CardWhileLabelHandler.main();
                });
                loadScript(config.przelewy24Card.scriptUrl.replace('%s', response.token));
            }).catch((error) => {
                this.messageContainer.addErrorMessage({
                    message: $t('Please refresh page and try again.')
                });
                console.log(error);
                loader.stopLoader();
            });
        },
    });
});
