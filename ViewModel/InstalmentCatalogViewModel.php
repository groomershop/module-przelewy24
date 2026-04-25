<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel;

class InstalmentCatalogViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig
     */
    private \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig $config;

    /**
     * @var \Magento\Framework\Registry
     */
    private \Magento\Framework\Registry $registry;

    public function __construct(
        \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig $config,
        \Magento\Framework\Registry $registry
    ) {
        $this->config = $config;
        $this->registry = $registry;
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled() && $this->config->isEnabledOnPdp();
    }

    public function getSerializedWidgetConfig(): string
    {
        return $this->config->getSerializedWidgetConfig(
            (float) $this->getProduct()->getPriceInfo()->getPrice('final_price')->getValue()
        );
    }

    public function isSimulatorEnabled(): bool
    {
        return $this->config->isSimulatorEnabled();
    }

    public function getWidgetSize(): string
    {
        return $this->config->getWidgetSize();
    }

    private function getProduct(): \Magento\Catalog\Model\Product
    {
        return $this->registry->registry('product');
    }
}
