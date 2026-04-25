<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class GatewayConfig extends \Magento\Payment\Gateway\Config\Config implements CurrencyConfigAwareInterface
{
    private const ACTIVE = 'active';
    private const PAYMENT_METHODS = 'payment_methods';
    private const SELECT_PAYMENT_METHOD_IN_STORE = 'select_payment_method_in_store';
    private const WAIT_FOR_TRANSACTION_RESULT = 'wait_for_transaction_result';
    private const INSTALMENT_MAP = 'instalment_map';
    private const PROMOTE_ERATY_SCB = 'promote_eraty_scb';

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, ConfigProvider::CODE, $pathPattern);
        $this->serializer = $serializer;
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

    public function getPaymentMethodsSortOrder(?int $storeId = null): ?array
    {
        try {
            $sortOrder = $this->serializer->unserialize((string) $this->getValue(self::PAYMENT_METHODS, $storeId));
        } catch (\InvalidArgumentException $e) {
            $sortOrder = [];
        }

        if (!is_array($sortOrder) || empty($sortOrder)) {
            return null;
        }

        return array_column($sortOrder, 'id');
    }

    public function isSelectPaymentMethodInStoreEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::SELECT_PAYMENT_METHOD_IN_STORE, $storeId);
    }

    public function isWaitForTransactionResultEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::WAIT_FOR_TRANSACTION_RESULT, $storeId);
    }

    public function getInstalmentMap(?int $storeId = null): ?array
    {
        try {
            $map = $this->serializer->unserialize((string) $this->getValue(self::INSTALMENT_MAP, $storeId));
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        return is_array($map) ? $map : [];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isERatySCBPromoted(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::PROMOTE_ERATY_SCB, $storeId);
    }

    public function getStandaloneMethods(?int $storeId = null): array
    {
        try {
            $methods = $this->serializer->unserialize((string) $this->getValue(self::PAYMENT_METHODS, $storeId));
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        if (!is_array($methods) || empty($methods)) {
            return [];
        }

        $standaloneMethods = [];

        foreach ($methods as $method) {
            $isStandalone = $method['standalone'] ?? false;
            if (!$isStandalone) {
                continue;
            }

            $standaloneMethods[$method['id']] = [
                'id' => $method['id'],
                'name' => $method['name'],
                'img' => $method['img'],
            ];
        }

        return $standaloneMethods;
    }
}
