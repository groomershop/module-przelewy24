define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'PayPro_Przelewy24/js/model/przelewy24-validator'
], function (Component, additionalValidators, przelewy24Validator) {
    'use strict';

    additionalValidators.registerValidator(przelewy24Validator);

    return Component.extend({});
});
