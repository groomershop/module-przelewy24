define([
    'jquery',
    'uiComponent',
    'ko',
    'jquery-ui-modules/sortable'
], function ($, Component, ko) {
    'use strict';

    const CREDIT_CARD_METHOD_ID_INPUT_SELECTOR = '[id$="przelewy24_gateway_payment_methods_cc_method_id"]';
    const APPLE_PAY_METHOD_ID_INPUT_SELECTOR = '[id$="przelewy24_gateway_payment_methods_ap_method_id"]';
    const GOOGLE_PAY_METHOD_ID_INPUT_SELECTOR = '[id$="przelewy24_gateway_payment_methods_gp_method_id"]';

    ko.bindingHandlers.sortable = {
        init: function (element, valueAccessor) {
            const elements = valueAccessor();
            $(element).sortable({
                axis: 'y',
                handle: '.draggable-handle',
                update: function (e, ui) {
                    const position = ko.utils.arrayIndexOf(ui.item.parent().children(), ui.item[0]);
                    if (position !== -1) {
                        const element = ko.dataFor(ui.item[0]);
                        elements.remove(element);
                        elements.splice(position, 0, element);
                    }

                    ui.item.remove();
                }.bind(this),
                tolerance: 'pointer'
            })
        }
    };

    return Component.extend({
        defaults: {
            template: 'PayPro_Przelewy24/payment-methods',
            sortableSelector: '#przelewy24-payment-methods',
            fieldName: null,
            updateUrl: null
        },

        methods: ko.observableArray([]),

        initialize: function (config) {
            this._super();
            this.methods(config.data);
        },

        clear: function () {
            this.methods([]);
        },

        update: function () {
            $.ajax({
                url: this.updateUrl,
                showLoader: true,
                dataType: 'json',
                method: 'GET'
            }).done(function (response) {
                this.updateMethods(response);
                this.updateMethodIds(response);
            }.bind(this));
        },

        updateMethods: function (apiMethods) {
            const methodIds = this.methods().map(function (method) {
                return method.id;
            });

            const apiMethodIds = apiMethods.map(function (method) {
                return method.id;
            });

            apiMethods.filter(function (method) {
                return methodIds.indexOf(method.id) === -1;
            }).forEach(function (method) {
                this.methods().push({
                    id: method.id,
                    name: method.name,
                    img: method.img_url,
                    fieldName: this.fieldName,
                    standalone: method.standalone
                });
            }.bind(this));

            this.methods.remove(function (method) {
                return apiMethodIds.indexOf(method.id) === -1;
            });

            this.methods.valueHasMutated();
        },

        updateMethodIds: function (methods) {
            this.updateMethodIdField(methods, ['Credit Card', 'Karta płatnicza'], CREDIT_CARD_METHOD_ID_INPUT_SELECTOR);
            this.updateMethodIdField(methods, ['Apple Pay', 'ApplePay'], APPLE_PAY_METHOD_ID_INPUT_SELECTOR);
            this.updateMethodIdField(methods, ['Google Pay', 'GooglePay'], GOOGLE_PAY_METHOD_ID_INPUT_SELECTOR);
        },

        updateMethodIdField: function (methods, fieldNames, fieldSelector) {
            const method = methods.find(method => fieldNames.includes(method.name));
            if (method && method.id) {
                $(fieldSelector).val(method.id);
            }
        }
    });
});
