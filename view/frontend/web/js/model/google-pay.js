define([
    'PayPro_Przelewy24/js/model/przelewy24-config',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators'
], function (config, quote, additionalValidators) {
    'use strict';

    let paymentsClient = null;

    const baseRequest = {
        apiVersion: 2,
        apiVersionMinor: 0
    };

    const tokenizationSpecification = {
        type: 'PAYMENT_GATEWAY',
        parameters: {
            'gateway': 'przelewy24',
            'gatewayMerchantId': String(config.przelewy24GooglePay.merchantId)
        }
    };

    const baseCardPaymentMethod = {
        type: 'CARD',
        parameters: {
            allowedAuthMethods: config.przelewy24GooglePay.authMethods,
            allowedCardNetworks: config.przelewy24GooglePay.cardNetworks
        }
    };

    const cardPaymentMethod = Object.assign(
        { tokenizationSpecification: tokenizationSpecification },
        baseCardPaymentMethod
    );

    return {
        isReadyToPayRequest: function () {
            return Object.assign({}, baseRequest, {
                allowedPaymentMethods: [baseCardPaymentMethod]
            });
        },

        getPaymentDataRequest: function () {
            const paymentDataRequest = Object.assign({}, baseRequest);
            paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
            paymentDataRequest.merchantInfo = {
                merchantId: String(config.przelewy24GooglePay.merchantId),
                merchantName: String(config.przelewy24GooglePay.merchantName)
            };

            return paymentDataRequest;
        },

        getPaymentsClient: function () {
            if ( paymentsClient === null ) {
                paymentsClient = new google.payments.api.PaymentsClient({
                    environment: config.przelewy24GooglePay.environment
                });
            }

            return paymentsClient;
        },

        createButton: function (onSuccess, buttonContainerId) {
            const paymentDataRequest = this.getPaymentDataRequest();
            const button = this.getPaymentsClient().createButton({
                onClick: function () {
                    if (!additionalValidators.validate()) {
                        return;
                    }

                    paymentDataRequest.transactionInfo = {
                        countryCode: quote.billingAddress().countryId,
                        currencyCode: quote.getTotals()().quote_currency_code,
                        totalPrice: String(quote.getTotals()().grand_total),
                        totalPriceStatus: 'FINAL'
                    };

                    paymentsClient.loadPaymentData(paymentDataRequest).then(onSuccess).catch(function (error) {
                        console.error(error);
                    });
                }
            });
            document.getElementById(buttonContainerId).appendChild(button);
        },
    };
});
