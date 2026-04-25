<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Checkout;

class Przelewy24CheckoutLayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig
     */
    private \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig $instalmentWidgetConfig;

    public function __construct(
        \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig $instalmentWidgetConfig
    ) {
        $this->instalmentWidgetConfig = $instalmentWidgetConfig;
    }

    public function process($jsLayout): array
    {
        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
            ['itemsAfter']['children']['przelewy24-instalment']['config'])) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['itemsAfter']
            ['children']['przelewy24-instalment']['config']['componentDisabled']
                = !($this->instalmentWidgetConfig->isEnabled() && $this->instalmentWidgetConfig->isEnabledOnCheckout());

            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['itemsAfter']
            ['children']['przelewy24-instalment']['config']['payload']
                = $this->instalmentWidgetConfig->getWidgetConfig();

            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['itemsAfter']
            ['children']['przelewy24-instalment']['config']['simulator']
                = $this->instalmentWidgetConfig->isSimulatorEnabled();
        }

        return $jsLayout;
    }
}
