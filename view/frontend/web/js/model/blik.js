define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, $t, modal) {
    'use strict';

    return {
        checkBlikStatus: function (url, sessionId) {
            return new Promise((resolve, reject) => {
                const intervalMs = 5000;
                const durationMs = 120000;
                let elapsedTime = 0;

                const intervalId = setInterval(() => {
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            sessionId: sessionId,
                        }),
                        showLoader: false,
                        method: 'POST',
                        success: function (response) {
                            if (response?.error) {
                                clearInterval(intervalId);
                                return response.error === '0' ? resolve(response) : reject(new Error(response.message));
                            }

                            elapsedTime += intervalMs;
                            if (elapsedTime >= durationMs) {
                                clearInterval(intervalId);
                                reject(new Error($t('The confirmation time has expired.')));
                            }
                        },
                        error: function () {
                            clearInterval(intervalId);
                            reject(new Error($t('Transaction has been declined. Please try again later.')));
                        }
                    });
                }, intervalMs);
            });
        },

        createBlikModal: function (defaultMessage) {
            return modal({
                type: 'popup',
                modalClass: 'przelewy24-blik-confirmation-modal',
                outerClickHandler: () => {},
                title: $t('BLIK payment'),
                buttons: []
            }, $('<div></div>').html(`<p>${defaultMessage}</p>`));
        },

        isBlikCodeReady: function (code) {
            return code && code.length === 6 && !/\D/.test(code);
        },
    };
});
