<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class ApplePayConfig extends \Magento\Payment\Gateway\Config\Config implements CurrencyConfigAwareInterface
{
    private const ACTIVE = 'active';
    private const CERTIFICATE = 'certificate';
    private const SSL_KEY = 'ssl_key';
    private const MERCHANT_IDENTIFIER = 'merchant_identifier';
    private const SSL_FILES_PATH = 'przelewy24_apple_pay';
    private const METHOD_ID = 'ap_method_id';

    const INITIATIVE = 'web';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, ConfigProvider::APPLE_PAY_CODE, $pathPattern);
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
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

    public function getDisplayName(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMerchantIdentifier(?int $storeId = null): string
    {
        return (string) $this->getValue(
            self::MERCHANT_IDENTIFIER,
            $storeId
        );
    }

    public function getInitiativeContext(?int $storeId = null): string
    {
        $baseUrl = $this->scopeConfig->getValue(Store::XML_PATH_SECURE_BASE_URL, ScopeInterface::SCOPE_STORE, $storeId);

        return (string) (new \Laminas\Uri\Http($baseUrl))->getHost();
    }

    public function getSSLKeyFilePath(?int $storeId = null): ?string
    {
        $configValue = $this->getValue(
            self::SSL_KEY,
            $storeId
        );

        if ($configValue === null) {
            return null;
        }

        return $this->getPemFileAbsolutePath($configValue);
    }

    public function getCertificateFilePath(?int $storeId = null): ?string
    {
        $configValue = $this->getValue(
            self::CERTIFICATE,
            $storeId
        );

        if ($configValue === null) {
            return null;
        }

        return $this->getPemFileAbsolutePath($configValue);
    }

    public function getMethodId(?int $storeId = null): ?int
    {
        return $this->getValue(self::METHOD_ID, $storeId) === null
            ? null
            : (int) $this->getValue(self::METHOD_ID, $storeId);
    }

    private function getPemFileAbsolutePath(string $path): string
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath(self::SSL_FILES_PATH . \DIRECTORY_SEPARATOR . $path);
    }
}
