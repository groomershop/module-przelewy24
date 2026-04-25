define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Vault/js/view/payment/vault-enabler',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'PayPro_Przelewy24/js/model/blik'
], function ($, Component, VaultEnabler, config, placeOrderAction, quote, additionalValidators, $t, modal, blikModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-blik',
            sessionId: null,
            blikCode: null
        },

        paymentDeferredObject: null,

        initialize: function () {
            this._super();

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.isActivePaymentTokenEnabler(false);
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'sessionId',
                'blikCode',
            ]);

            return this;
        },

        getCode: function () {
            return 'przelewy24_blik';
        },

        getLogo: function () {
            return config.przelewy24Blik.logoUrl;
        },

        getData: function () {
            const data = {
                method: this.getCode(),
                additional_data: {
                    blikCode: this.blikCode(),
                    sessionId: this.sessionId(),
                }
            };

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        getPlaceOrderDeferredObject: function () {
            this.paymentDeferredObject = $.Deferred();

            $.when(placeOrderAction(this.getData(), this.messageContainer)).then(function () {
                const confirmationModal = blikModel.createBlikModal(this.registerTransactionMessage);
                confirmationModal.openModal();

                blikModel.checkBlikStatus(config.przelewy24Blik.checkBlikStatusUrl, this.sessionId()).then(() => {
                    this.paymentDeferredObject.resolve();
                }).catch((error) => {
                    confirmationModal.element.html(error.message);
                    setTimeout(() => this.paymentDeferredObject.resolve(), config.przelewy24Blik.confirmationErrorTime || 5000);
                });
            }.bind(this));

            return $.when(this.paymentDeferredObject);
        },

        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        getVaultCode: function () {
            return config.przelewy24Blik.vaultCode;
        },

        isBlikCodeReady: function () {
            return blikModel.isBlikCodeReady(this.blikCode());
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        },

        registerTransactionAndPlaceOrder: function () {
            if (!additionalValidators.validate()) {
                return;
            }

            $.ajax({
                url: config.przelewy24Blik.registerTransactionUrl,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    cartId: quote.getQuoteId(),
                    blikCode: this.blikCode(),
                    saveAlias: this.vaultEnabler.isActivePaymentTokenEnabler()
                }),
                showLoader: true,
                method: 'POST'
            }).done(function (response) {
                if (response.success) {
                    this.sessionId(response.session_id);
                    this.registerTransactionMessage = response.message;
                    this.placeOrder();

                    return;
                }
                this.messageContainer.addErrorMessage({
                    message: response.message
                });
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
        }
    });
});
