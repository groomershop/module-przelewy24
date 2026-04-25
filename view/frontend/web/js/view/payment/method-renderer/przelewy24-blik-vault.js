define([
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'mage/translate'
], function (Component, config, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment/przelewy24-blik-vault'
        },

        getToken: function () {
            return this.publicHash;
        },

        getCode: function () {
            return 'przelewy24_blik_vault';
        },

        getRegulations: function () {
            return config.przelewy24.regulations;
        },

        getIcon: function () {
            return config.przelewy24Blik.logoUrl;
        },

        getName: function () {
            return $t('Alias for %1').replace('%1', this.details.email);
        }
    });
});
