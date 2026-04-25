<?php
declare(strict_types=1);

namespace Magento\PhpStan\PayPro\Przelewy24\Test\Unit\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Model\IsPaymentExpired;
use PHPUnit\Framework\TestCase;

class IsPaymentExpiredTest extends TestCase
{
    const PAYMENT_ID = '1';
    const ORDER_ID = '2';

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = $this->createMock(CommonConfig::class);
        $this->transactionRepositoryMock = $this->createMock(Repository::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentMock->expects($this->any())->method('getEntityId')->willReturn(self::PAYMENT_ID);
        $this->orderMock = $this->createMock(Order::class);
        $this->orderMock->expects($this->any())->method('getEntityId')->willReturn(self::ORDER_ID);
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($this->orderMock);

        $this->model = new IsPaymentExpired($this->configMock, $this->transactionRepositoryMock);
    }

    public function testExecute(): void
    {
        $this->paymentMock->expects($this->once())->method('getLastTransId')->willReturn('uuid');
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->once())->method('getCreatedAt')->willReturn('2020-01-01 10:00:00');
        $this->configMock->expects($this->once())->method('getPaymentTimeLimit')->willReturn(10);
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with('uuid', (int) self::PAYMENT_ID, (int) self::ORDER_ID)
            ->willReturn($transactionMock);

        $this->assertTrue($this->model->execute($this->paymentMock));
    }

    public function testExecuteNoTimeLimit(): void
    {
        $this->configMock->expects($this->once())->method('getPaymentTimeLimit')->willReturn(0);
        $this->transactionRepositoryMock->expects($this->never())->method('getByTransactionId');

        $this->assertFalse($this->model->execute($this->paymentMock));
    }

    public function testExecuteException(): void
    {
        $this->paymentMock->expects($this->once())->method('getLastTransId')->willReturn('uuid');
        $this->configMock->expects($this->once())->method('getPaymentTimeLimit')->willReturn(30);
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with('uuid', self::PAYMENT_ID, self::ORDER_ID)
            ->willThrowException(new \Exception('Error'));

        $this->assertFalse($this->model->execute($this->paymentMock));
    }
}
