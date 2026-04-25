<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Config;

use Magento\Store\Model\ScopeInterface;

class InstalmentWidgetConfig
{
    private const XPATH_INSTALMENT_WIDGET_ENABLED = 'payment/przelewy24_instalment_widget/enabled';
    private const XPATH_INSTALMENT_WIDGET_SHOW_ON_PDP = 'payment/przelewy24_instalment_widget/show_on_pdp';
    private const XPATH_INSTALMENT_WIDGET_SHOW_ON_CART = 'payment/przelewy24_instalment_widget/show_on_cart';
    private const XPATH_INSTALMENT_WIDGET_SHOW_ON_CHECKOUT = 'payment/przelewy24_instalment_widget/show_on_checkout';
    private const XPATH_INSTALMENT_WIDGET_METHOD_ID = 'payment/przelewy24_instalment_widget/method_id';
    private const XPATH_INSTALMENT_WIDGET_CURRENCY = 'payment/przelewy24_instalment_widget/currency';
    private const XPATH_INSTALMENT_WIDGET_LANG = 'payment/przelewy24_instalment_widget/lang';
    private const XPATH_INSTALMENT_WIDGET_BANNER_SIZE = 'payment/przelewy24_instalment_widget/banner_size';
    private const XPATH_INSTALMENT_WIDGET_SHOW_SIMULATOR = 'payment/przelewy24_instalment_widget/show_simulator';

    private const SIGN = 'sign';
    private const POS_ID = 'posid';
    private const METHOD = 'method';
    private const AMOUNT = 'amount';
    private const CURRENCY = 'currency';
    private const LANG = 'lang';
    private const TEST = 'test';
    private const CMS = 'cms';

    private const CMS_MAGENTO = 'mag';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private \PayPro\Przelewy24\Gateway\Config\CommonConfig $commonConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private \Magento\Framework\Serialize\Serializer\Json $json;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $commonConfig,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commonConfig = $commonConfig;
        $this->json = $json;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XPATH_INSTALMENT_WIDGET_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isEnabledOnPdp(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XPATH_INSTALMENT_WIDGET_SHOW_ON_PDP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isEnabledOnCart(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XPATH_INSTALMENT_WIDGET_SHOW_ON_CART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isEnabledOnCheckout(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XPATH_INSTALMENT_WIDGET_SHOW_ON_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSign(?int $storeId = null): string
    {
        return hash('sha384', (string) json_encode(
            [
                'crc' => $this->commonConfig->getCrcKey($storeId),
                'posId' => $this->commonConfig->getPosId($storeId),
                'method' => (int) $this->getMethodId($storeId),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
    }

    public function getPosId(?int $storeId = null): int
    {
        return $this->commonConfig->getPosId($storeId);
    }

    public function getMethodId(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_INSTALMENT_WIDGET_METHOD_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCurrency(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_INSTALMENT_WIDGET_CURRENCY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getLang(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_INSTALMENT_WIDGET_LANG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getWidgetSize(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_INSTALMENT_WIDGET_BANNER_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isSimulatorEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XPATH_INSTALMENT_WIDGET_SHOW_SIMULATOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getWidgetConfig(?float $amount = null, ?int $storeId = null): array
    {
        $config = [
            self::SIGN => $this->getSign($storeId),
            self::POS_ID => (string) $this->getPosId($storeId),
            self::METHOD => $this->getMethodId($storeId),
            self::CURRENCY => $this->getCurrency($storeId),
            self::LANG => $this->getLang($storeId),
            self::TEST => $this->commonConfig->isTestMode($storeId),
            self::CMS => self::CMS_MAGENTO,
        ];

        if ($amount !== null) {
            $config[self::AMOUNT] = (int) round($amount * 100);
        }

        return $config;
    }

    public function getSerializedWidgetConfig(?float $amount = null, ?int $storeId = null): string
    {
        return (string) $this->json->serialize($this->getWidgetConfig($amount, $storeId));
    }
}
