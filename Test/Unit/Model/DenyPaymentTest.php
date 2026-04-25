<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\TransactionRepositoryInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PayPro\Przelewy24\Model\DenyPayment;
use PayPro\Przelewy24\Model\IsPaymentExpired;
use PayPro\Przelewy24\Model\IsPaymentMade;
use PHPUnit\Framework\TestCase;

class DenyPaymentTest extends TestCase
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
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentMade|\PHPUnit\Framework\MockObject\MockObject
     */
    private $isPaymentMadeMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired|\PHPUnit\Framework\MockObject\MockObject
     */
    private $isPaymentExpiredMock;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiTransaction
     */
    private $transaction;

    /**
     * @var \PayPro\Przelewy24\Model\DenyPayment
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);
        $this->transactionRepositoryMock = $this->getMockForAbstractClass(TransactionRepositoryInterface::class);
        $this->transactionRepositoryMock->expects($this->once())->method('get')->willReturn($this->transactionMock);
        $this->paymentDataObjectMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->isPaymentMadeMock = $this->createMock(IsPaymentMade::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects($this->any())->method('getStoreId')->willReturn(1);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setIsTransactionDenied'])
            ->onlyMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($orderMock);
        $this->isPaymentExpiredMock = $this->createMock(IsPaymentExpired::class);
        $this->transaction = new ApiTransaction(['sessionId' => 'uuid']);

        $this->model = new DenyPayment(
            $this->transactionRepositoryMock,
            $this->isPaymentMadeMock,
            $this->isPaymentExpiredMock
        );
    }

    public function testExecute(): void
    {
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(false);
        $this->paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->with('uuid', 1)->willReturn(false);
        $this->isPaymentExpiredMock->expects($this->once())
            ->method('execute')
            ->with($this->paymentMock)
            ->willReturn(true);
        $this->transactionMock->expects($this->once())->method('setIsClosed')->with(1);
        $this->paymentMock->expects($this->once())->method('setIsTransactionDenied')->with(true);

        $this->model->execute($this->paymentDataObjectMock, $this->transaction);
    }

    public function testExecutePaidTransaction(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Payment made, it can\'t be denied.');
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(false);
        $this->paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->with('uuid', 1)->willReturn(true);
        $this->transactionMock->expects($this->never())->method('setIsClosed');
        $this->paymentMock->expects($this->never())->method('setIsTransactionDenied');

        $this->model->execute($this->paymentDataObjectMock, $this->transaction);
    }

    public function testExecuteTransactionInProgress(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Payment in progress, it can\'t be denied');
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(false);
        $this->paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->with('uuid', 1)->willReturn(false);
        $this->isPaymentExpiredMock->expects($this->once())
            ->method('execute')
            ->with($this->paymentMock)
            ->willReturn(false);
        $this->transactionMock->expects($this->never())->method('setIsClosed');
        $this->paymentMock->expects($this->never())->method('setIsTransactionDenied');

        $this->model->execute($this->paymentDataObjectMock, $this->transaction);
    }

    public function testExecuteClosedTransaction(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Transaction uuid is already closed');
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(true);

        $this->model->execute($this->paymentDataObjectMock, $this->transaction);
    }
}
