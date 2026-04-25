<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Modifier;

use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;

class PaymentMethodsSortOrderModifier implements PaymentMethodsModifierInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GatewayConfig
     */
    private $config;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\GatewayConfig $config
    ) {
        $this->config = $config;
    }

    public function modify(array $paymentMethods): array
    {
        $paymentMethodsSortOrder = $this->config->getPaymentMethodsSortOrder();
        if ($paymentMethodsSortOrder === null) {
            return $paymentMethods;
        }

        $sortOrder = array_flip($paymentMethodsSortOrder);

        usort($paymentMethods, function (
            ApiPaymentMethodInterface $a,
            ApiPaymentMethodInterface $b
        ) use ($sortOrder) {
            $aPosition = $sortOrder[$a->getId()] ?? INF;
            $bPosition = $sortOrder[$b->getId()] ?? INF;

            return $aPosition <=> $bPosition;
        });

        return $paymentMethods;
    }
}
