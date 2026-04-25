<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Modifier;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;
use PayPro\Przelewy24\Model\Modifier\PaymentMethodsCompositeModifier;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodsCompositeModifierTest extends TestCase
{
    public function testModify(): void
    {
        $paymentMethods = [
            new ApiPaymentMethod(1, 'method_1'),
            new ApiPaymentMethod(2, 'method_2'),
        ];

        $modifiedPaymentMethods = [
            new ApiPaymentMethod(1, 'modified_method_1'),
            new ApiPaymentMethod(2, 'modified_method_2'),
        ];

        $paymentMethodsModifierMock = $this->getMockForAbstractClass(PaymentMethodsModifierInterface::class);
        $model = new PaymentMethodsCompositeModifier([$paymentMethodsModifierMock]);
        $paymentMethodsModifierMock->expects($this->once())->method('modify')->willReturn($modifiedPaymentMethods);

        $this->assertEquals($modifiedPaymentMethods, $model->modify($paymentMethods));
    }

    public function testInvalidModifier(): void
    {
        $this->expectException(LocalizedException::class);
        $model = new PaymentMethodsCompositeModifier([new DataObject()]);
        $model->modify([]);
    }
}
