define([
    '//apm.przelewy24.pl/installments/installment-calculator-app.umd.sdk.js'
], function () {
    return async function (config, element) {
        const { widgetConfig, size, simulator, customTrigger } = config;
        const installmentCalculatorApp = new InstallmentCalculatorApp(widgetConfig);

        if (simulator) {
            const calculatorModal = await installmentCalculatorApp.create('calculator-modal');
            calculatorModal.render('przelewy24-instalment-calculator-modal');
        }

        if (customTrigger) {
            const trigger = document.getElementById(element.id);
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modal = document.getElementById('installment-calculator-modal');
                if (modal) {
                    modal.style.display = 'block';
                }
            });
        } else {
            const widget = await installmentCalculatorApp.create(`${size}-widget`);
            widget.render(element.id);
        }

    };
});
