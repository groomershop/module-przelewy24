<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Modifier;

use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;
use PayPro\Przelewy24\Gateway\Config\BlikConfig;

class PaymentMethodsBlikModifier implements PaymentMethodsModifierInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\BlikConfig
     */
    private $blikConfig;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\BlikConfig $blikConfig
    ) {
        $this->blikConfig = $blikConfig;
    }

    public function modify(array $paymentMethods): array
    {
        if ($this->isInStoreBlikEnabled()) {
            return $this->withoutInStoreBlik($paymentMethods);
        }

        return $paymentMethods;
    }

    private function isInStoreBlikEnabled(): bool
    {
        return $this->blikConfig->isActive();
    }

    private function withoutInStoreBlik(array $paymentMethods): array
    {
        return array_filter($paymentMethods, function (ApiPaymentMethodInterface $paymentMethod) {
            return $paymentMethod->getId() !== BlikConfig::BLIK_IN_STORE_ID;
        });
    }
}
