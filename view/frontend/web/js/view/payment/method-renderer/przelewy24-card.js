define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Vault/js/view/payment/vault-enabler',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader',
    'PayPro_Przelewy24/js/utils/script-loader',
    'PayPro_Przelewy24/js/utils/iframe-remove',
    'Magento_Customer/js/model/customer'
], function ($, Component, VaultEnabler, config, quote, additionalValidators, $t, modal, loader, loadScript, iframeRemove, customer) {
    'use strict';

    function isP24ReadyMessageEvent(data) {
        return data?.p24 && data.type === 'ready';
    }

    function isP24SuccessMessageEvent(data) {
        return data?.p24 && data.type === 'success';
    }

    function isP24FailMessageEvent(data) {
        return data?.p24 && data.type === 'fail';
    }

    const P24TargetElementId = 'p24-cc-modal';
    const interactionModalSelector = '#' + P24TargetElementId;

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-card',
            isPaymentReady: false,
            sessionId: null,
            refId: null,
            cardType: null,
            cardDate: null,
            cardMask: null
        },

        isFormRendered: false,

        initialize: function () {
            this._super();

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.isActivePaymentTokenEnabler(false);
            this.vaultEnabler.setPaymentCode(this.getVaultCode());


            if (quote.paymentMethod() && quote.paymentMethod().method === 'przelewy24_card') {
                this.renderCardForm();
            }

            quote.paymentMethod.subscribe((paymentMethod) => {
                if (paymentMethod.method === 'przelewy24_card') {
                    this.renderCardForm();
                }
            });

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'isPaymentReady',
                'refId',
                'cardType',
                'cardDate',
                'cardMask',
                'sessionId'
            ]);

            return this;
        },

        getCode: function () {
            return 'przelewy24_card';
        },

        getLogo: function () {
            return config.przelewy24Card.logoUrl;
        },

        getData: function () {
            const data = {
                method: this.getCode(),
                additional_data: this.getAdditionalData()
            };

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        isClickToPayEnabled: function () {
            if (window.checkoutConfig.isCustomerLoggedIn) {
                return !!config.przelewy24Card.c2p;
            }

            return !!config.przelewy24Card.c2pGuests;
        },

        getCustomerEmail: function () {
            return quote.guestEmail || customer.customerData?.email;
        },

        getVaultCode: function () {
            return config.przelewy24Card.vaultCode;
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        },

        getAdditionalData: function () {
            const additionalData = {
                sessionId: this.sessionId()
            };

            if (this.vaultEnabler.isActivePaymentTokenEnabler()) {
                additionalData.refId = this.refId();
                additionalData.cardType = this.cardType();
                additionalData.cardDate = this.cardDate();
                additionalData.cardMask = this.cardMask();
            }

            return additionalData;
        },

        getCardFormOptions: function () {
            return {
                lang: config.przelewy24Card.storeLang,
                c2p: this.isClickToPayEnabled(),
                psu: {
                    email: this.getCustomerEmail()
                },
                size: {
                    height: '400px'
                }
            };
        },

        registerTransaction: function (token) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: config.przelewy24Card.registerTransactionUrl,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({ cartId: quote.getQuoteId(), sessionId: this.sessionId(), token }),
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

        renderCardForm: function () {
            if (this.isFormRendered) {
                return;
            }

            loadScript(config.przelewy24Card.tokenizationScriptUrl).then(() => {
                $.ajax({
                    url: config.przelewy24Card.tokenizationPayloadUrl,
                    dataType: 'json',
                    contentType: 'application/json',
                    showLoader: true,
                    method: 'GET'
                }).done((response) =>{
                    this.isFormRendered = true;
                    modal(config.przelewy24InteractionModalConfig, $(interactionModalSelector));
                    window.addEventListener('message', (e) => this.handleP24MessageEvent(e.data));
                    this.sessionId(response.session_id);
                    this.Przelewy24CardTokenization = new Przelewy24CardTokenization(
                        response.merchant_id,
                        response.session_id,
                        response.signature
                    );
                    this.Przelewy24CardTokenization.render('form', '#p24-cc-form', this.getCardFormOptions());
                }).fail((response) => {
                    if (response.responseJSON && response.responseJSON.message) {
                        this.messageContainer.addErrorMessage({
                            message: response.responseJSON.message
                        });

                        return;
                    }

                    this.messageContainer.addErrorMessage({
                        message: $t('Please refresh page and try again.')
                    });
                });
            });
        },

        handleP24MessageEvent: function (data) {
            if (isP24ReadyMessageEvent(data)) {
                this.updatePaymentReadyState(data);
            }

            if (isP24FailMessageEvent(data)) {
                console.error(data.errors);
                loader.stopLoader();
                this.messageContainer.addErrorMessage({
                    message: $t('Transaction has been declined. Please refresh page and try again.')
                });
            }

            if (isP24SuccessMessageEvent(data)) {
                this.submitPaymentAndPlaceOrder(data.data);
            }
        },

        updatePaymentReadyState: function (data) {
            this.isPaymentReady(data.status);
        },

        submitPaymentAndPlaceOrder: function (data) {
            if (this.vaultEnabler.isActivePaymentTokenEnabler()) {
                this.refId(data.refId);
                this.cardType(data.cardType);
                this.cardDate(data.cardDate);
                this.cardMask(data.mask);
            }

            this.registerTransaction(data.refId).then((response) => {
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

        payWithCard: function () {
            if (!additionalValidators.validate()) {
                return;
            }

            loader.startLoader();

            const tokenizationMode = this.vaultEnabler.isActivePaymentTokenEnabler() ? 'permanent' : 'temporary';
            this.Przelewy24CardTokenization.tokenize(tokenizationMode);
        }
    });
});
