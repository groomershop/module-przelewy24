<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class GooglePayConfig extends \Magento\Payment\Gateway\Config\Config implements CurrencyConfigAwareInterface
{
    private const ACTIVE = 'active';
    private const MERCHANT_ID = 'merchant_id';
    private const AUTH_METHODS = 'authentication_methods';
    private const CARD_NETWORKS = 'card_networks';
    private const METHOD_ID = 'gp_method_id';

    const TEST_MODE = 'TEST';
    const PRODUCTION_MODE = 'PRODUCTION';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, ConfigProvider::GOOGLE_PAY_CODE, $pathPattern);
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

    public function getMerchantId(?int $storeId = null): string
    {
        return (string) $this->getValue(self::MERCHANT_ID, $storeId);
    }

    public function getAuthMethods(?int $storeId = null): array
    {
        return array_filter(explode(',', (string) $this->getValue(self::AUTH_METHODS, $storeId)));
    }

    public function getCardNetworks(?int $storeId = null): array
    {
        return array_filter(explode(',', (string) $this->getValue(self::CARD_NETWORKS, $storeId)));
    }

    public function getMethodId(?int $storeId = null): ?int
    {
        return $this->getValue(self::METHOD_ID, $storeId) === null
            ? null
            : (int) $this->getValue(self::METHOD_ID, $storeId);
    }
}
