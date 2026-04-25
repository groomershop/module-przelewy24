define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate'
], function ($, alert) {
    'use strict';

    $.widget('przelewy24.testAccess', {
        options: {
            url: null,
            scopeType: 'default',
            scopeId: null
        },

        _create: function () {
            this.element.on('click', this._testAccess.bind(this));
        },

        _testAccess: function () {
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: {
                    scope_type: this.options.scopeType,
                    scope_id: this.options.scopeId
                }
            }).always(function (response) {
                alert({ content: this._getTestResult(response) });
            }.bind(this));
        },

        _getTestResult: function (response) {
            if (!response) {
                return $.mage.__('Connection error!');
            }

            if (response.data === true) {
                return $.mage.__('Test was successful!');
            }

            return response.error;
        }
    });

    return $.przelewy24.testAccess;
});
