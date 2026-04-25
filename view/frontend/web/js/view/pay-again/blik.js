define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'PayPro_Przelewy24/js/model/blik'
], function ($, Component, $t, blikModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/pay-again/blik',
            blikCode: null,
            sessionId: null,
            cartId: null,
            blikConfig: {},
            errorMessage: '',
            successMessage: '',
            defaultError: ''
        },

        initialize: function () {
            this._super();

            this.errorMessage(this.defaultError);

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'sessionId',
                'blikCode',
                'errorMessage',
                'successMessage'
            ]);

            return this;
        },

        isBlikCodeReady: function () {
            return blikModel.isBlikCodeReady(this.blikCode());
        },

        payAgain: function () {
            this.errorMessage('');
            $.ajax({
                url: this.blikConfig.payAgainUrl,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    sessionId: this.sessionId(),
                    blikCode: this.blikCode(),
                }),
                showLoader: true,
                method: 'POST'
            }).done(function (response) {
                if (response.success) {
                    const confirmationModal = blikModel.createBlikModal(response.message);
                    confirmationModal.openModal();

                    this.sessionId(response.session_id);
                    blikModel.checkBlikStatus(this.blikConfig.checkBlikStatusUrl, this.sessionId()).then(() => {
                        this.successMessage($t('The payment was processed correctly.'));
                    }).catch((error) => {
                        this.errorMessage(error.message);
                        this.blikCode(null);
                    }).finally(() => {
                        confirmationModal.closeModal();
                    });

                    return;
                }
                this.errorMessage(response.message);
                this.blikCode(null);
            }.bind(this)).fail(function (response) {
                if (response.responseJSON && response.responseJSON.message) {
                    this.errorMessage(response.responseJSON.message);

                    return;
                }

                this.errorMessage($t('Please try again.'));
            }.bind(this));
        }
    });
});
