<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Modifier;

use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PayPro\Przelewy24\Model\Modifier\PaymentMethodsStandaloneModifier;
use PHPUnit\Framework\TestCase;

class PaymentMethodsStandaloneModifierTest extends TestCase
{
    public function testModify(): void
    {
        $configMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('getStandaloneMethods')->willReturn([
            2 => [],
            5 => [],
            6 => [],
        ]);

        $paymentMethod1 = new ApiPaymentMethod(1);
        $paymentMethod2 = new ApiPaymentMethod(2);
        $paymentMethod3 = new ApiPaymentMethod(3);
        $paymentMethod4 = new ApiPaymentMethod(4);
        $paymentMethod5 = new ApiPaymentMethod(5);
        $paymentMethod6 = new ApiPaymentMethod(6);

        $model = new PaymentMethodsStandaloneModifier($configMock);
        $model->modify([
            $paymentMethod1,
            $paymentMethod2,
            $paymentMethod3,
            $paymentMethod4,
            $paymentMethod5,
            $paymentMethod6,
        ]);

        $this->assertFalse($paymentMethod1->isStandalone());
        $this->assertTrue($paymentMethod2->isStandalone());
        $this->assertFalse($paymentMethod3->isStandalone());
        $this->assertFalse($paymentMethod4->isStandalone());
        $this->assertTrue($paymentMethod5->isStandalone());
        $this->assertTrue($paymentMethod6->isStandalone());
    }
}
