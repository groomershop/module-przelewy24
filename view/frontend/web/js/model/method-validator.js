define([
    'PayPro_Przelewy24/js/model/apple-pay'
], function (ApplePay) {
    'use strict';

    const validators = {
        232: function () {
            return ApplePay.isSupported();
        },
        239: function () {
            return ApplePay.isSupported();
        },
        252: function () {
            return ApplePay.isSupported();
        },
        253: function () {
            return ApplePay.isSupported();
        }
    };

    return {
        registerValidator: function (methodId, validator) {
            validators[methodId] = validator;
        },

        validate: function (methodId) {
            if (!validators[methodId]) {
                return true;
            }

            return validators[methodId]();
        }
    };
});
