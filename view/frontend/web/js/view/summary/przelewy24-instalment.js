define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    '//apm.przelewy24.pl/installments/installment-calculator-app.umd.sdk.js'
], function (Component, quote) {
    'use strict';

    const totals = quote.getTotals();

    return Component.extend({
        initializeInstalment: async function() {
            this.initCalculatorApp(totals()?.grand_total);

            totals.subscribe((newValue) => {
                this.initCalculatorApp(newValue.grand_total)
            });
        },
        initCalculatorApp: async function (amount) {
            const installmentCalculatorApp = new InstallmentCalculatorApp({
                ...this.payload,
                amount: Math.round(amount * 100)
            });

            const calculatorModal = await installmentCalculatorApp.create('calculator-modal');
            calculatorModal.render('przelewy24-instalment-calculator-modal');
        },
        openModal: function() {
            const modal = document.getElementById('installment-calculator-modal');
            if (modal) {
                modal.style.display = 'block';
            }
        }
    });
});
