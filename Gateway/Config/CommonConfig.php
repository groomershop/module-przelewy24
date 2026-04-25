<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;

class CommonConfig
{
    private const MERCHANT_ID = 'payment/przelewy24/merchant_id';
    private const POS_ID = 'payment/przelewy24/pos_id';
    private const CRC_KEY = 'payment/przelewy24/crc_key';
    private const TEST_MODE = 'payment/przelewy24/test_mode';
    private const PRODUCTION_URL = 'payment/przelewy24/production_url';
    private const SANDBOX_URL = 'payment/przelewy24/sandbox_url';
    private const CARD_VAULT_ENABLED = 'payment/przelewy24_card_vault/active';
    private const CARD_PRODUCTION_SCRIPT_URL = 'payment/przelewy24_card/production_script_url';
    private const CARD_SANDBOX_SCRIPT_URL = 'payment/przelewy24_card/sandbox_script_url';
    private const TOKENIZATION_PRODUCTION_SCRIPT_URL = 'payment/przelewy24_card/production_tokenization_script_url';
    private const TOKENIZATION_SANDBOX_SCRIPT_URL = 'payment/przelewy24_card/sandbox_tokenization_script_url';
    private const GOOGLE_PAY_PRODUCTION_SCRIPT_URL = 'payment/przelewy24_google_pay/production_script_url';
    private const GOOGLE_PAY_SANDBOX_SCRIPT_URL = 'payment/przelewy24_google_pay/sandbox_script_url';
    private const APPLE_PAY_PRODUCTION_SCRIPT_URL = 'payment/przelewy24_apple_pay/production_script_url';
    private const APPLE_PAY_SANDBOX_SCRIPT_URL = 'payment/przelewy24_apple_pay/sandbox_script_url';
    private const PAYMENT_AUTO_UPDATE = 'payment/przelewy24/payment_auto_update';
    private const PAYMENT_TIME_LIMIT = 'payment/przelewy24/payment_time_limit';
    private const PAYMENT_EXTRA_TIME_LIMIT = 'payment/przelewy24/payment_extra_time_limit';
    private const PAYMENT_EXTRA_TIME_LIMIT_METHODS = 'payment/przelewy24/payment_extra_time_limit_methods';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getMerchantId(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(self::MERCHANT_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMerchantName(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPosId(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(self::POS_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCrcKey(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(self::CRC_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isTestMode(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::TEST_MODE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isCardVaultEnabled(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CARD_VAULT_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getGatewayUrl(?int $storeId = null): string
    {
        if ($this->isTestMode($storeId)) {
            return $this->scopeConfig->getValue(self::SANDBOX_URL, ScopeInterface::SCOPE_STORE, $storeId);
        }

        return $this->scopeConfig->getValue(self::PRODUCTION_URL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCardScriptUrl(?int $storeId = null): string
    {
        if ($this->isTestMode($storeId)) {
            return $this->scopeConfig->getValue(self::CARD_SANDBOX_SCRIPT_URL, ScopeInterface::SCOPE_STORE, $storeId);
        }

        return $this->scopeConfig->getValue(self::CARD_PRODUCTION_SCRIPT_URL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCardTokenizationScriptUrl(?int $storeId = null): string
    {
        if ($this->isTestMode($storeId)) {
            return $this->scopeConfig->getValue(
                self::TOKENIZATION_SANDBOX_SCRIPT_URL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->scopeConfig->getValue(
            self::TOKENIZATION_PRODUCTION_SCRIPT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGooglePayScriptUrl(?int $storeId = null): string
    {
        if ($this->isTestMode($storeId)) {
            return $this->scopeConfig->getValue(
                self::GOOGLE_PAY_SANDBOX_SCRIPT_URL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->scopeConfig->getValue(
            self::GOOGLE_PAY_PRODUCTION_SCRIPT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApplePayScriptUrl(?int $storeId = null): string
    {
        if ($this->isTestMode($storeId)) {
            return $this->scopeConfig->getValue(
                self::APPLE_PAY_SANDBOX_SCRIPT_URL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->scopeConfig->getValue(
            self::APPLE_PAY_PRODUCTION_SCRIPT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isPaymentAutoUpdateEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PAYMENT_AUTO_UPDATE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getPaymentTimeLimit(?int $method, ?int $storeId = null): int
    {
        if (!$method) {
            return (int) $this->scopeConfig->getValue(self::PAYMENT_TIME_LIMIT, ScopeInterface::SCOPE_STORE, $storeId);
        }

        $extraTimeLimitMethods = array_map('intval', array_filter(explode(',', (string) $this->scopeConfig->getValue(
            self::PAYMENT_EXTRA_TIME_LIMIT_METHODS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ))));
        if (empty($extraTimeLimitMethods) || !in_array($method, $extraTimeLimitMethods)) {
            return (int) $this->scopeConfig->getValue(self::PAYMENT_TIME_LIMIT, ScopeInterface::SCOPE_STORE, $storeId);
        }

        return (int) $this->scopeConfig->getValue(
            self::PAYMENT_EXTRA_TIME_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
