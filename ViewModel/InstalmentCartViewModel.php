<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel;

class InstalmentCartViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig
     */
    private \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private \Magento\Checkout\Model\Session $checkoutSession;

    public function __construct(
        \PayPro\Przelewy24\Model\Config\InstalmentWidgetConfig $config,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled() && $this->config->isEnabledOnCart();
    }

    public function getSerializedWidgetConfig(): string
    {
        return $this->config->getSerializedWidgetConfig((float) $this->checkoutSession->getQuote()->getGrandTotal());
    }

    public function isSimulatorEnabled(): bool
    {
        return $this->config->isSimulatorEnabled();
    }
}
