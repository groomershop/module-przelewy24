define([
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/model/quote'
], function (config, quote) {
    'use strict';

    return {
        isSupported: function () {
            return location.protocol === 'https:' && window.ApplePaySession && ApplePaySession.canMakePayments();
        },

        getPaymentRequest: function () {
            return {
                countryCode: quote.billingAddress().countryId,
                currencyCode: quote.getTotals()().quote_currency_code,
                supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
                merchantCapabilities: ['supports3DS'],
                total: {
                    label: config.przelewy24ApplePay.merchantName,
                    amount: String(quote.getTotals()().grand_total)
                }
            };
        }
    };
});
