<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\PaymentException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PayPro\Przelewy24\Model\IsPaymentExpired;
use PayPro\Przelewy24\Model\IsPaymentMade;
use PayPro\Przelewy24\Model\PayAgain;
use PayPro\Przelewy24\Model\RenewTransaction;
use PHPUnit\Framework\TestCase;

class RenewTransactionTest extends TestCase
{
    /**
     * @var \Magento\Framework\Api\Filter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterBuilderMock;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroup|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterGroupMock;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterGroupBuilderMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionCollectionMock;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionMock;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentMade|\PHPUnit\Framework\MockObject\MockObject
     */
    private $isPaymentMadeMock;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired|\PHPUnit\Framework\MockObject\MockObject
     */
    private $isPaymentExpiredMock;

    /**
     * @var \PayPro\Przelewy24\Model\PayAgain|\PHPUnit\Framework\MockObject\MockObject
     */
    private $payAgainMock;

    /**
     * @var \PayPro\Przelewy24\Model\RenewTransaction
     */
    private $model;

    protected function setUp(): void
    {
        $this->filterMock = $this->createMock(Filter::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->filterBuilderMock->expects($this->any())->method('setField')->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())->method('setValue')->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())->method('setConditionType')->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())->method('create')->willReturn($this->filterMock);
        $this->filterGroupMock = $this->createMock(FilterGroup::class);
        $this->filterGroupBuilderMock = $this->createMock(FilterGroupBuilder::class);
        $this->filterGroupBuilderMock->expects($this->any())->method('addFilter')->willReturnSelf();
        $this->filterGroupBuilderMock->expects($this->any())->method('create')->willReturn($this->filterGroupMock);
        $this->searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('setFilterGroups')->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->transactionCollectionMock = $this->createMock(Collection::class);
        $this->transactionRepositoryMock = $this->createMock(TransactionRepositoryInterface::class);
        $this->transactionRepositoryMock->expects($this->once())->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->transactionCollectionMock);
        $this->transactionMock = $this->createMock(TransactionInterface::class);
        $this->paymentRepositoryMock = $this->createMock(OrderPaymentRepositoryInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->isPaymentMadeMock = $this->createMock(IsPaymentMade::class);
        $this->isPaymentExpiredMock = $this->createMock(IsPaymentExpired::class);
        $this->payAgainMock = $this->createMock(PayAgain::class);

        $this->model = new RenewTransaction(
            $this->filterBuilderMock,
            $this->filterGroupBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->transactionRepositoryMock,
            $this->paymentRepositoryMock,
            $this->isPaymentMadeMock,
            $this->isPaymentExpiredMock,
            $this->payAgainMock
        );
    }

    public function testExecute(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $orderMock->expects($this->once())->method('isPaymentReview')->willReturn(true);
        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->transactionCollectionMock->expects($this->once())->method('getLastItem')
            ->willReturn($this->transactionMock);
        $this->transactionCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->transactionMock]));
        $this->paymentRepositoryMock->expects($this->once())->method('get')->willReturn($this->paymentMock);
        $this->transactionMock->expects($this->once())->method('getTxnId')->willReturn('uuid');
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($orderMock);
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->willReturn(false);
        $this->isPaymentExpiredMock->expects($this->once())->method('execute')->willReturn(false);
        $this->payAgainMock->expects($this->once())->method('execute');

        $this->model->execute('uuid');
    }

    public function testExecuteNoTransaction(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Can\'t renew transaction, transaction not found.');
        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(0);
        $this->payAgainMock->expects($this->never())->method('execute');

        $this->model->execute('uuid');
    }

    public function testExecuteDifferentPaymentProvider(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Can\'t renew transaction, different payment provider.');
        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->transactionCollectionMock->expects($this->once())->method('getLastItem')
            ->willReturn($this->transactionMock);
        $this->transactionCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->transactionMock]));
        $this->paymentRepositoryMock->expects($this->once())->method('get')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('custom_online_payment');
        $this->payAgainMock->expects($this->never())->method('execute');

        $this->model->execute('uuid');
    }

    public function testExecutePaymentAlreadyMade(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Can\'t renew transaction, payment already made.');
        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->transactionCollectionMock->expects($this->once())->method('getLastItem')
            ->willReturn($this->transactionMock);
        $this->transactionCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->transactionMock]));
        $this->transactionMock->expects($this->once())->method('getTxnId')->willReturn('uuid');
        $this->paymentRepositoryMock->expects($this->once())->method('get')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->willReturn(true);
        $this->payAgainMock->expects($this->never())->method('execute');

        $this->model->execute('uuid');
    }

    public function testExecutePaymentAlreadyProcessed(): void
    {
        $this->paymentRepositoryMock->expects($this->once())->method('get')->willReturn($this->paymentMock);
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Can\'t renew transaction, payment already processed.');
        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->transactionCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->transactionMock]));
        $this->transactionCollectionMock->expects($this->once())->method('getLastItem')
            ->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getTxnId')->willReturn('uuid');
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($orderMock);
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->willReturn(false);
        $orderMock->expects($this->once())->method('isPaymentReview')->willReturn(false);
        $this->payAgainMock->expects($this->never())->method('execute');

        $this->model->execute('uuid');
    }

    public function testExecutePaymentExpired(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Can\'t renew transaction, payment expired.');
        $this->paymentRepositoryMock->expects($this->once())->method('get')->willReturn($this->paymentMock);
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($orderMock);
        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->transactionCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->transactionMock]));
        $this->transactionCollectionMock->expects($this->once())->method('getLastItem')
            ->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getTxnId')->willReturn('uuid');
        $this->isPaymentMadeMock->expects($this->once())->method('execute')->willReturn(false);
        $orderMock->expects($this->once())->method('isPaymentReview')->willReturn(true);
        $this->isPaymentExpiredMock->expects($this->once())->method('execute')->willReturn(true);
        $this->payAgainMock->expects($this->never())->method('execute');

        $this->model->execute('uuid');
    }
}
