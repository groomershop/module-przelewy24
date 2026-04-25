<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Gateway\Config\GooglePayConfig;

class ConfigProvider implements ConfigProviderInterface
{
    public const PAYMENT = 'payment';

    const CODE = 'przelewy24';
    const CARD_CODE = 'przelewy24_card';
    const CARD_VAULT_CODE = 'przelewy24_card_vault';
    const BLIK_CODE = 'przelewy24_blik';
    const BLIK_VAULT_CODE = 'przelewy24_blik_vault';
    const GOOGLE_PAY_CODE = 'przelewy24_google_pay';
    const APPLE_PAY_CODE = 'przelewy24_apple_pay';

    private const PAYMENT_REDIRECT_ROUTE = 'przelewy24/payment/redirect';
    private const PAYMENT_METHODS_URL = 'przelewy24/payment-methods';
    private const REGISTER_CARD_TRANSACTION_URL = 'przelewy24/register-card-transaction';
    private const REGISTER_CARD_VAULT_TRANSACTION_URL = 'przelewy24/register-card-vault-transaction';
    private const TOKENIZATION_PAYLOAD_URL = 'przelewy24/get-card-tokenization-payload';
    private const REGISTER_BLIK_TRANSACTION_URL = 'przelewy24/register-blik-transaction';
    private const CHECK_BLIK_STATUS_URL = 'przelewy24/blik-notification';
    private const BLIK_PAY_AGAIN_URL = 'przelewy24/blik-pay';
    private const REGISTER_GOOGLE_PAY_TRANSACTION_URL = 'przelewy24/register-google-pay-transaction';
    private const REGISTER_APPLE_PAY_TRANSACTION_URL = 'przelewy24/register-apple-pay-transaction';

    private const TOKENIZATION_CMS = 'cms';
    private const TOKENIZATION_CMS_MAGENTO = 'mag';
    private const TOKENIZATION_MERCHANT_ID = 'mid';
    private const TOKENIZATION_ONE_CLICK = 'oc';
    private const TOKENIZATION_CLICK_2_PAY = 'c2p';
    private const TOKENIZATION_PARAM_TRUE = 'true';
    private const TOKENIZATION_PARAM_FALSE = 'false';

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $commonConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CardConfig
     */
    private $cardConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\BlikConfig
     */
    private $blikConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GooglePayConfig
     */
    private $googlePayConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\ApplePayConfig
     */
    private $applePayConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \PayPro\Przelewy24\Model\Ui\Logo
     */
    private $logo;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $commonConfig,
        \PayPro\Przelewy24\Gateway\Config\GatewayConfig $gatewayConfig,
        \PayPro\Przelewy24\Gateway\Config\CardConfig $cardConfig,
        \PayPro\Przelewy24\Gateway\Config\BlikConfig $blikConfig,
        \PayPro\Przelewy24\Gateway\Config\GooglePayConfig $googlePayConfig,
        \PayPro\Przelewy24\Gateway\Config\ApplePayConfig $applePayConfig,
        \Magento\Framework\UrlInterface $url,
        \PayPro\Przelewy24\Model\Ui\Logo $logo,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->commonConfig = $commonConfig;
        $this->gatewayConfig = $gatewayConfig;
        $this->cardConfig = $cardConfig;
        $this->blikConfig = $blikConfig;
        $this->googlePayConfig = $googlePayConfig;
        $this->applePayConfig = $applePayConfig;
        $this->url = $url;
        $this->logo = $logo;
        $this->localeResolver = $localeResolver;
    }

    public function getConfig(): array
    {
        $regulationsLink = __('<a target="_blank" href="https://www.przelewy24.pl/regulations">regulations</a>');
        $gdprLink = __(
            '<a target="_blank" href="https://www.przelewy24.pl/information-obligation-gdpr-payer">'
            . 'information obligation</a>'
        );

        return [
            self::PAYMENT => [
                self::CODE => [
                    'isActive' => $this->gatewayConfig->isActive(),
                    'paymentRedirectUrl' => $this->url->getUrl(self::PAYMENT_REDIRECT_ROUTE),
                    'logoUrl' => $this->logo->getUrl(),
                    'paymentMethodsUrl' => $this->getRestUrl(self::PAYMENT_METHODS_URL),
                    'regulations' => __(
                        'I hereby state that I have read the %1 and %2 of Przelewy24.',
                        $regulationsLink,
                        $gdprLink
                    ),
                    'isSelectPaymentMethodInStoreEnabled' =>
                        $this->gatewayConfig->isSelectPaymentMethodInStoreEnabled(),
                    'isERatySCBPromoted' => $this->gatewayConfig->isERatySCBPromoted(),
                    'ERatySCBId' => ApiPaymentMethodInterface::ERATY_SCB_ID,
                    'standaloneMethods' => $this->gatewayConfig->getStandaloneMethods(),
                    'instalmentMap' => $this->gatewayConfig->getInstalmentMap(),
                ],
                self::CARD_CODE => [
                    'merchantId' => $this->commonConfig->getMerchantId(),
                    'isActive' => $this->cardConfig->isActive(),
                    'logoUrl' => $this->logo->getCardUrl(),
                    'vaultCode' => self::CARD_VAULT_CODE,
                    'storeLang' => strstr($this->localeResolver->getLocale(), '_', true) ?: 'en',
                    'scriptUrl' => $this->commonConfig->getCardScriptUrl(),
                    'tokenizationScriptUrl' => $this->getTokenizationScriptUrl(),
                    'tokenizationPayloadUrl' => $this->getRestUrl(self::TOKENIZATION_PAYLOAD_URL),
                    'registerTransactionUrl' => $this->getRestUrl(self::REGISTER_CARD_TRANSACTION_URL),
                    'registerVaultTransactionUrl' => $this->getRestUrl(self::REGISTER_CARD_VAULT_TRANSACTION_URL),
                    'c2p' => $this->cardConfig->isC2pEnabled(),
                    'c2pGuests' => $this->cardConfig->isC2pEnabledForGuests(),
                ],
                self::BLIK_CODE => [
                    'isActive' => $this->blikConfig->isActive(),
                    'confirmationErrorTime' => $this->blikConfig->getConfirmationErrorTime(),
                    'logoUrl' => $this->logo->getBlikUrl(),
                    'vaultCode' => self::BLIK_VAULT_CODE,
                    'registerTransactionUrl' => $this->getRestUrl(self::REGISTER_BLIK_TRANSACTION_URL),
                    'checkBlikStatusUrl' => $this->getRestUrl(self::CHECK_BLIK_STATUS_URL),
                    'payAgainUrl' => $this->getRestUrl(self::BLIK_PAY_AGAIN_URL),
                ],
                self::GOOGLE_PAY_CODE => [
                    'isActive' => $this->googlePayConfig->isActive(),
                    'logoUrl' => $this->logo->getGooglePayUrl(),
                    'merchantId' => $this->googlePayConfig->getMerchantId(),
                    'merchantName' => $this->commonConfig->getMerchantName(),
                    'authMethods' => $this->googlePayConfig->getAuthMethods(),
                    'cardNetworks' => $this->googlePayConfig->getCardNetworks(),
                    'environment' => $this->commonConfig->isTestMode()
                        ? GooglePayConfig::TEST_MODE
                        : GooglePayConfig::PRODUCTION_MODE,
                    'registerTransactionUrl' => $this->getRestUrl(self::REGISTER_GOOGLE_PAY_TRANSACTION_URL),
                    'scriptUrl' => $this->commonConfig->getGooglePayScriptUrl(),
                ],
                self::APPLE_PAY_CODE => [
                    'isActive' => $this->applePayConfig->isActive(),
                    'logoUrl' => $this->logo->getApplePayUrl(),
                    'merchantName' => $this->commonConfig->getMerchantName(),
                    'registerTransactionUrl' => $this->getRestUrl(self::REGISTER_APPLE_PAY_TRANSACTION_URL),
                    'scriptUrl' => $this->commonConfig->getApplePayScriptUrl(),
                ],
            ],
        ];
    }

    private function getRestUrl(string $path): string
    {
        return rtrim($this->url->getUrl('rest/V1'), '/') . '/' . $path;
    }

    private function getTokenizationScriptUrl(): string
    {
        return $this->commonConfig->getCardTokenizationScriptUrl() . '?' . http_build_query([
            self::TOKENIZATION_CMS => self::TOKENIZATION_CMS_MAGENTO,
            self::TOKENIZATION_MERCHANT_ID => $this->commonConfig->getMerchantId(),
            self::TOKENIZATION_ONE_CLICK => $this->commonConfig->isCardVaultEnabled()
                ? self::TOKENIZATION_PARAM_TRUE
                : self::TOKENIZATION_PARAM_FALSE,
            self::TOKENIZATION_CLICK_2_PAY => $this->cardConfig->isC2pEnabled()
                ? self::TOKENIZATION_PARAM_TRUE
                : self::TOKENIZATION_PARAM_FALSE,
        ]);
    }
}
