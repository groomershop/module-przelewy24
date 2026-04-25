define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list',
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'PayPro_Przelewy24/js/model/method-validator',
    'Magento_Checkout/js/model/quote',
    'PayPro_Przelewy24/js/model/apple-pay'
], function (Component, rendererList, config, methodValidator, quote, ApplePay) {
    'use strict';

    function isMethodAvailable(methodId) {
        const instalmentMap = config.przelewy24.instalmentMap;
        if (!instalmentMap || !instalmentMap[methodId]) {
            return true;
        }
        const grandTotal = quote.getTotals()().grand_total;

        return grandTotal >= instalmentMap[methodId].from && grandTotal <= instalmentMap[methodId].to;
    }

    if (config.przelewy24.isActive) {
        const standaloneMethods = config.przelewy24.standaloneMethods;

        if (typeof standaloneMethods === 'object' && standaloneMethods !== null) {
            for (const prop in standaloneMethods) {
                if (Object.prototype.hasOwnProperty.call(standaloneMethods, prop)
                    && isMethodAvailable(standaloneMethods[prop].id)
                    && methodValidator.validate(standaloneMethods[prop].id)) {
                    rendererList.push(
                        {
                            type: 'przelewy24_' + standaloneMethods[prop].id,
                            component: 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-standalone',
                            config: {
                                methodId: standaloneMethods[prop].id,
                                title: standaloneMethods[prop].name,
                                logoUrl: standaloneMethods[prop].img
                            },
                            typeComparatorCallback: function (type, method) {
                                return method === 'przelewy24';
                            }
                        }
                    );
                }
            }
        }

        rendererList.push(
            {
                type: 'przelewy24',
                component: 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24'
            }
        );
    }

    if (config.przelewy24Card.isActive) {
        rendererList.push(
            {
                type: 'przelewy24_card',
                component: 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-card'
            }
        );
    }

    if (config.przelewy24Blik.isActive) {
        rendererList.push(
            {
                type: 'przelewy24_blik',
                component: 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-blik'
            }
        );
    }

    if (config.przelewy24GooglePay.isActive) {
        rendererList.push(
            {
                type: 'przelewy24_google_pay',
                component: 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-google-pay'
            }
        );
    }

    if (config.przelewy24ApplePay.isActive && ApplePay.isSupported()) {
        rendererList.push(
            {
                type: 'przelewy24_apple_pay',
                component: 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-apple-pay'
            }
        );
    }

    return Component.extend({});
});
