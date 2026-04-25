<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PayPro\Przelewy24\Model\DenyExpiredPayment;
use PayPro\Przelewy24\Model\DenyPayment;
use PayPro\Przelewy24\Model\IsPaymentExpired;
use PHPUnit\Framework\TestCase;

class DenyExpiredPaymentTest extends TestCase
{
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired|\PHPUnit\Framework\MockObject\MockObject
     */
    private $isPaymentExpiredMock;

    /**
     * @var \PayPro\Przelewy24\Model\DenyPayment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $denyPaymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\DenyExpiredPayment
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentDataObjectMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->isPaymentExpiredMock = $this->createMock(IsPaymentExpired::class);
        $this->denyPaymentMock = $this->createMock(DenyPayment::class);

        $this->model = new DenyExpiredPayment($this->isPaymentExpiredMock, $this->denyPaymentMock);
    }

    public function testExecute(): void
    {
        $this->isPaymentExpiredMock->expects($this->once())->method('execute')->willReturn(true);
        $transaction = new ApiTransaction(['sessionId' => 'uuid']);
        $this->denyPaymentMock->expects($this->once())->method('execute');

        $this->model->execute($this->paymentDataObjectMock, $transaction);
    }

    public function testNoExecute(): void
    {
        $this->isPaymentExpiredMock->expects($this->once())->method('execute')->willReturn(false);
        $transaction = new ApiTransaction(['sessionId' => 'uuid']);
        $this->denyPaymentMock->expects($this->never())->method('execute');

        $this->model->execute($this->paymentDataObjectMock, $transaction);
    }
}
