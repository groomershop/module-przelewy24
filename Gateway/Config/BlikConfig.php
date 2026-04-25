<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class BlikConfig extends \Magento\Payment\Gateway\Config\Config implements CurrencyConfigAwareInterface
{
    public const BLIK_REDIRECT_ID = 154;
    public const BLIK_IN_STORE_ID = 181;

    private const ACTIVE = 'active';
    private const CONFIRMATION_ERROR_TIME = 'confirmation_error_time';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, ConfigProvider::BLIK_CODE, $pathPattern);
    }

    public function isActive(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::ACTIVE, $storeId);
    }

    public function isCurrencyAllowed(string $currencyCode, ?int $storeId = null): bool
    {
        $allowedCurrencies = explode(',', (string) $this->getValue(self::ALLOWED_CURRENCIES, $storeId));

        return in_array($currencyCode, $allowedCurrencies);
    }

    public function getConfirmationErrorTime(?int $storeId = null): int
    {
        return (int) $this->getValue(self::CONFIRMATION_ERROR_TIME, $storeId);
    }
}
