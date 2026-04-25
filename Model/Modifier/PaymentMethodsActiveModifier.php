<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Modifier;

use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;

class PaymentMethodsActiveModifier implements PaymentMethodsModifierInterface
{
    public function modify(array $paymentMethods): array
    {
        return array_filter($paymentMethods, function (ApiPaymentMethodInterface $method) {
            return $method->isActive() === true;
        });
    }
}
