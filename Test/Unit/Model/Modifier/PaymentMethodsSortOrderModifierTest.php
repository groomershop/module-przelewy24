<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Modifier;

use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Model\Modifier\PaymentMethodsSortOrderModifier;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodsSortOrderModifierTest extends TestCase
{
    public function testModify(): void
    {
        $sortOrder = ['5', '7', '2', '1', '29', '99999999'];

        $paymentMethod1 = new ApiPaymentMethod(1);
        $paymentMethod2 = new ApiPaymentMethod(2);
        $paymentMethod3 = new ApiPaymentMethod(3);
        $paymentMethod4 = new ApiPaymentMethod(4);
        $paymentMethod5 = new ApiPaymentMethod(5);
        $paymentMethod6 = new ApiPaymentMethod(6);
        $paymentMethod7 = new ApiPaymentMethod(7);
        $paymentMethod99999999 = new ApiPaymentMethod(99999999);

        $configMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('getPaymentMethodsSortOrder')->willReturn($sortOrder);
        $model = new PaymentMethodsSortOrderModifier($configMock);

        $this->assertEquals([
            $paymentMethod5,
            $paymentMethod7,
            $paymentMethod2,
            $paymentMethod1,
            $paymentMethod99999999,
            $paymentMethod3,
            $paymentMethod4,
            $paymentMethod6,
        ], $model->modify([
            $paymentMethod1,
            $paymentMethod2,
            $paymentMethod3,
            $paymentMethod4,
            $paymentMethod5,
            $paymentMethod6,
            $paymentMethod7,
            $paymentMethod99999999,
        ]));
    }

    public function testModifyWithoutConfiguredSortOrder(): void
    {
        $paymentMethod = new ApiPaymentMethod();
        $configMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('getPaymentMethodsSortOrder')->willReturn(null);

        $model = new PaymentMethodsSortOrderModifier($configMock);

        $this->assertEquals([$paymentMethod], $model->modify([$paymentMethod]));
    }
}
