<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Modifier;

use PayPro\Przelewy24\Model\Modifier\PaymentMethodsActiveModifier;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodsActiveModifierTest extends TestCase
{
    public function testModify(): void
    {
        $payment1 = new ApiPaymentMethod(1, 'payment1', false);
        $payment2 = new ApiPaymentMethod(2, 'payment2', true);
        $payment3 = new ApiPaymentMethod(3, 'payment3', true);
        $payment4 = new ApiPaymentMethod(4, 'payment4', false);

        $model = new PaymentMethodsActiveModifier();

        $this->assertEquals([
            1 => $payment2,
            2 => $payment3,
        ], $model->modify([$payment1, $payment2, $payment3, $payment4]));
    }
}
