<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class CardConfig extends \Magento\Payment\Gateway\Config\Config implements CurrencyConfigAwareInterface
{
    private const ACTIVE = 'active';
    private const METHOD_ID = 'cc_method_id';
    private const C2P = 'c2p';
    private const C2P_GUESTS = 'c2p_guests';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, ConfigProvider::CARD_CODE, $pathPattern);
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

    public function getMethodId(?int $storeId = null): ?int
    {
        return $this->getValue(self::METHOD_ID, $storeId) === null
            ? null
            : (int) $this->getValue(self::METHOD_ID, $storeId);
    }

    public function isC2pEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::C2P, $storeId);
    }

    public function isC2pEnabledForGuests(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::C2P_GUESTS, $storeId);
    }
}
