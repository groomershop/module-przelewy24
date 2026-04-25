define(function () {
    'use strict';

    const przelewy24 = window.checkoutConfig.payment.przelewy24;
    const przelewy24Card = window.checkoutConfig.payment.przelewy24_card;
    const przelewy24Blik = window.checkoutConfig.payment.przelewy24_blik;
    const przelewy24GooglePay = window.checkoutConfig.payment.przelewy24_google_pay;
    const przelewy24ApplePay = window.checkoutConfig.payment.przelewy24_apple_pay;

    return {
        przelewy24: przelewy24 || {},
        przelewy24Card: przelewy24Card || {},
        przelewy24Blik: przelewy24Blik || {},
        przelewy24GooglePay: przelewy24GooglePay || {},
        przelewy24ApplePay: przelewy24ApplePay || {},
        przelewy24InteractionModalConfig: {
            type: 'popup',
            title: '',
            modalClass: 'przelewy24-interaction-modal',
            responsive: true,
            clickableOverlay: false,
            closeOnEscape: false,
            buttons: []
        }
    };
});
