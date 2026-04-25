define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    return {
        validate: function () {
            return $.validator.validateSingleElement($('.przelewy24-regulations-accept:visible'), {
                errorElement: 'div'
            });
        }
    };
});
