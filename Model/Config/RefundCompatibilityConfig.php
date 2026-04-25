<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Config;

class RefundCompatibilityConfig
{
    const IS_ENABLED = 'payment/przelewy24/refund_compatibility_enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::IS_ENABLED);
    }
}
