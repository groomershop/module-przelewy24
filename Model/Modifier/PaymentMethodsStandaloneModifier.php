<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Modifier;

use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;

class PaymentMethodsStandaloneModifier implements PaymentMethodsModifierInterface
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
        $standaloneMethods = $this->config->getStandaloneMethods();

        foreach ($paymentMethods as $paymentMethod) {
            if (isset($standaloneMethods[$paymentMethod->getId()])) {
                $paymentMethod->setIsStandalone(true);
            }
        }

        return $paymentMethods;
    }
}
