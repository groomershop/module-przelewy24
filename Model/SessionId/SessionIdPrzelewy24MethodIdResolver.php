<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\SessionId;

use PayPro\Przelewy24\Gateway\Config\BlikConfig;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class SessionIdPrzelewy24MethodIdResolver
{
    const SESSION_ID_PART_WITH_METHOD = 'dirmet{%s}_';
    const SESSION_ID_PART_WITHOUT_METHOD = 'pg_';
    const PAYMENT_ADDITIONAL_METHOD = 'method';

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\ApplePayConfig
     */
    private $applePayConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GooglePayConfig
     */
    private $googlePayConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CardConfig
     */
    private $cardConfig;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\ApplePayConfig $applePayConfig,
        \PayPro\Przelewy24\Gateway\Config\GooglePayConfig $googlePayConfig,
        \PayPro\Przelewy24\Gateway\Config\CardConfig $cardConfig
    ) {
        $this->applePayConfig = $applePayConfig;
        $this->googlePayConfig = $googlePayConfig;
        $this->cardConfig = $cardConfig;
    }

    public function resolve(string $paymentMethod, ?array $paymentAdditional = []): string
    {
        if (!empty($paymentAdditional[self::PAYMENT_ADDITIONAL_METHOD])) {
            return sprintf(
                self::SESSION_ID_PART_WITH_METHOD,
                $paymentAdditional[self::PAYMENT_ADDITIONAL_METHOD]
            );
        }

        $methodMap = $this->getMethodMap();

        if (!empty($methodMap[$paymentMethod])) {
            return sprintf(
                self::SESSION_ID_PART_WITH_METHOD,
                $methodMap[$paymentMethod]
            );
        }

        return $paymentMethod === ConfigProvider::CODE ? self::SESSION_ID_PART_WITHOUT_METHOD : '';
    }

    private function getMethodMap(): array
    {
        return [
            ConfigProvider::CARD_CODE => $this->cardConfig->getMethodId(),
            ConfigProvider::CARD_VAULT_CODE => $this->cardConfig->getMethodId(),
            ConfigProvider::BLIK_CODE => BlikConfig::BLIK_IN_STORE_ID,
            ConfigProvider::BLIK_VAULT_CODE => BlikConfig::BLIK_IN_STORE_ID,
            ConfigProvider::GOOGLE_PAY_CODE => $this->googlePayConfig->getMethodId(),
            ConfigProvider::APPLE_PAY_CODE => $this->applePayConfig->getMethodId(),
        ];
    }
}
