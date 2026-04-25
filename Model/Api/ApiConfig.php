<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use Magento\Store\Model\ScopeInterface;

class ApiConfig
{
    const URL = 'url';
    const USERNAME = 'username';
    const PASSWORD = 'password';

    private const XPATH_API_USERNAME = 'payment/przelewy24/pos_id';
    private const XPATH_API_PASSWORD = 'payment/przelewy24/report_key';
    private const XPATH_API_PRODUCTION_URL = 'payment/przelewy24/production_url';
    private const XPATH_API_SANDBOX_URL = 'payment/przelewy24/sandbox_url';
    private const XPATH_API_TEST_MODE = 'payment/przelewy24/test_mode';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function get(string $scopeType = ScopeInterface::SCOPE_STORE, ?int $scopeId = null): array
    {
        $username = $this->scopeConfig->getValue(self::XPATH_API_USERNAME, $scopeType, $scopeId);
        $password = $this->scopeConfig->getValue(self::XPATH_API_PASSWORD, $scopeType, $scopeId);

        $url = $this->scopeConfig->isSetFlag(self::XPATH_API_TEST_MODE, $scopeType, $scopeId)
            ? $this->scopeConfig->getValue(self::XPATH_API_SANDBOX_URL, $scopeType, $scopeId)
            : $this->scopeConfig->getValue(self::XPATH_API_PRODUCTION_URL, $scopeType, $scopeId);

        return [
            self::URL => (string) $url,
            self::USERNAME => (string) $username,
            self::PASSWORD => (string) $password,
        ];
    }
}
