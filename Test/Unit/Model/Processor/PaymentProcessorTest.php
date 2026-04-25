<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\TransactionRepositoryInterface;
use PayPro\Przelewy24\Model\ClosePayment;
use PayPro\Przelewy24\Model\Processor\PaymentProcessor;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PHPUnit\Framework\TestCase;

class PaymentProcessorTest extends TestCase
{
    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionMock;

    /**
     * @var \PayPro\Przelewy24\Api\TransactionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\ClosePayment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $closePaymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\PaymentProcessor
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);
        $this->transactionRepositoryMock = $this->getMockForAbstractClass(TransactionRepositoryInterface::class);
        $this->paymentRepositoryMock = $this->getMockForAbstractClass(OrderPaymentRepositoryInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->createMock(Order::class);
        $this->closePaymentMock = $this->createMock(ClosePayment::class);

        $orderMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($orderMock);

        $this->model = new PaymentProcessor(
            $this->transactionRepositoryMock,
            $this->paymentRepositoryMock,
            $this->closePaymentMock
        );
    }

    public function testProcess(): void
    {
        $transaction = new ApiTransaction(['sessionId' => 'uuid']);

        $this->transactionRepositoryMock->expects($this->once())->method('get')->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(false);
        $this->transactionMock->expects($this->once())->method('getPaymentId')->willReturn('1');
        $this->paymentRepositoryMock->expects($this->once())->method('get')->with(1)->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('accept');
        $this->paymentMock->expects($this->once())->method('capture');
        $this->transactionRepositoryMock->expects($this->once())->method('save');
        $this->closePaymentMock->expects($this->once())->method('execute');

        $this->assertSame($this->paymentMock, $this->model->process($transaction));
    }

    public function testProcessClosedTransaction(): void
    {
        $transaction = new ApiTransaction(['sessionId' => 'uuid']);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Transaction uuid is already closed');

        $this->transactionRepositoryMock->expects($this->once())->method('get')->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(true);
        $this->closePaymentMock->expects($this->never())->method('execute');

        $this->model->process($transaction);
    }
}
