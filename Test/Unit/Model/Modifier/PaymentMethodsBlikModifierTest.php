<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Modifier;

use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PayPro\Przelewy24\Model\Modifier\PaymentMethodsBlikModifier;
use PHPUnit\Framework\TestCase;

class PaymentMethodsBlikModifierTest extends TestCase
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\BlikConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $blikConfigMock;

    /**
     * @var \PayPro\Przelewy24\Model\Modifier\PaymentMethodsBlikModifier
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blikConfigMock = $this->createMock(\PayPro\Przelewy24\Gateway\Config\BlikConfig::class);
        $this->model = new PaymentMethodsBlikModifier($this->blikConfigMock);
    }

    public function testInStoreBlikDisabled(): void
    {
        $this->blikConfigMock->expects($this->once())->method('isActive')->willReturn(false);

        $payment1 = new ApiPaymentMethod(1, 'payment1');
        $payment2 = new ApiPaymentMethod(2, 'payment2');
        $payment3 = new ApiPaymentMethod(154, 'Blik redirect');
        $payment4 = new ApiPaymentMethod(181, 'Blik in store');

        $this->assertEquals([
            0 => $payment1,
            1 => $payment2,
            2 => $payment3,
            3 => $payment4,
        ], $this->model->modify([$payment1, $payment2, $payment3, $payment4]));
    }

    public function testBlikRemoved(): void
    {
        $this->blikConfigMock->expects($this->once())->method('isActive')->willReturn(true);

        $payment1 = new ApiPaymentMethod(1, 'payment1');
        $payment2 = new ApiPaymentMethod(2, 'payment2');
        $payment3 = new ApiPaymentMethod(154, 'Blik redirect');
        $payment4 = new ApiPaymentMethod(181, 'Blik in store');

        $this->assertEquals([
            0 => $payment1,
            1 => $payment2,
            2 => $payment3,
        ], $this->model->modify([$payment1, $payment2, $payment3, $payment4]));
    }

    public function testBlikNotRemoved(): void
    {
        $this->blikConfigMock->expects($this->once())->method('isActive')->willReturn(true);

        $payment1 = new ApiPaymentMethod(1, 'payment1');
        $payment2 = new ApiPaymentMethod(2, 'payment2');
        $payment3 = new ApiPaymentMethod(153, 'payment3');
        $payment4 = new ApiPaymentMethod(154, 'Blik in store');

        $this->assertEquals([
            0 => $payment1,
            1 => $payment2,
            2 => $payment3,
            3 => $payment4,
        ], $this->model->modify([$payment1, $payment2, $payment3, $payment4]));
    }
}
