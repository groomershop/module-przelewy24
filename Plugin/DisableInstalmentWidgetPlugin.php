<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Plugin;

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Api\ApiClientInterface;

class DisableInstalmentWidgetPlugin
{
    private const PRZELEWY24_SECTION_PREFIX = 'przelewy24_';
    private const INSTALMENT_METHOD_ID = 303;

    private const XPATH_PRZELEWY24_INSTALMENT_WIDGET_ENABLED = 'payment/przelewy24_instalment_widget/enabled';
    private const XPATH_PRZELEWY24_GATEWAY_ACTIVE = 'payment/przelewy24/active';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private \Magento\Framework\App\Config\Storage\WriterInterface $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->apiClientFactory = $apiClientFactory;
        $this->apiConfig = $apiConfig;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
    }

    public function afterSave(Config $subject): void
    {
        $scopeType = $this->getScopeType($subject);
        $scopeId = $this->getScopeId($subject);

        if (!$this->isPrzelewy24SectionSaved($subject)
            || !$this->isPrzelewy24Active($scopeType, $scopeId)
            || !$this->isInstalmentWidgetEnabled($scopeType, $scopeId)) {
            return;
        }

        try {
            $apiClient = $this->getApiClient($scopeType, $scopeId);

            if ($this->isApiConfigured($apiClient) && !$this->isInstalmentMethodAvailable($apiClient)) {
                $this->configWriter->save(
                    self::XPATH_PRZELEWY24_INSTALMENT_WIDGET_ENABLED,
                    '0',
                    $this->resolveWriterScope($scopeType),
                    $scopeId
                );
                $this->reinitableConfig->reinit();
            }
        } catch (\Throwable $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        }
    }

    private function isPrzelewy24SectionSaved(Config $config): bool
    {
        return strpos($config->getSection(), self::PRZELEWY24_SECTION_PREFIX) === 0;
    }

    private function getScopeType(Config $config): string
    {
        return $config->getWebsite()
            ? ScopeInterface::SCOPE_WEBSITE
            : ($config->getStore() ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    private function getScopeId(Config $config): int
    {
        if ($config->getWebsite()) {
            return (int) $config->getWebsite();
        }

        return (int) ($config->getStore() ?: null);
    }

    private function isPrzelewy24Active(string $scopeType, ?int $scopeId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_PRZELEWY24_GATEWAY_ACTIVE, $scopeType, $scopeId);
    }

    private function isInstalmentWidgetEnabled(string $scopeType, ?int $scopeId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_PRZELEWY24_INSTALMENT_WIDGET_ENABLED, $scopeType, $scopeId);
    }

    private function isApiConfigured(ApiClientInterface $apiClient): bool
    {
        $response = $apiClient->testAccess();

        return isset($response['data']);
    }

    private function isInstalmentMethodAvailable(ApiClientInterface $apiClient): bool
    {
        $paymentMethods = $apiClient->paymentMethods();
        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethodId = $paymentMethod['id'] ?? null;
            if ($paymentMethodId === self::INSTALMENT_METHOD_ID) {
                return true;
            }
        }

        return false;
    }

    private function getApiClient(string $scopeType, ?int $scopeId): ApiClientInterface
    {
        return $this->apiClientFactory->create(
            $this->apiConfig->get($scopeType, $scopeId)
        );
    }

    private function resolveWriterScope(string $scopeType): string
    {
        if ($scopeType === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return $scopeType;
        }

        /**
         * @see \Magento\Framework\App\Config::getValue
         */
        return rtrim($scopeType, 's') . 's';
    }
}
