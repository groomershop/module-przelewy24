<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Modifier;

use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;

class PaymentMethodsCompositeModifier implements PaymentMethodsModifierInterface
{
    /**
     * @var \PayPro\Przelewy24\Api\PaymentMethodsModifierInterface[]
     */
    private $modifiers;

    public function __construct(
        array $modifiers = []
    ) {
        $this->modifiers = $modifiers;
    }

    public function modify(array $paymentMethods): array
    {
        foreach ($this->modifiers as $modifier) {
            if (!$modifier instanceof PaymentMethodsModifierInterface) {
                throw new LocalizedException(__(
                    'Modifier %1 must implement %2 interface.',
                    get_class($modifier),
                    PaymentMethodsModifierInterface::class
                ));
            }

            $paymentMethods = $modifier->modify($paymentMethods);
        }

        return array_values($paymentMethods);
    }
}
