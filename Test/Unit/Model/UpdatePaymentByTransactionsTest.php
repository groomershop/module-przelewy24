<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PayPro\Przelewy24\Model\UpdatePaymentByTransactions;
use PHPUnit\Framework\TestCase;

class UpdatePaymentByTransactionsTest extends TestCase
{
    public function testExecute(): void
    {
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);
        $sortOrderMock = $this->createMock(SortOrder::class);
        $sortOrderBuilderMock->expects($this->any())->method('setField')->willReturnSelf();
        $sortOrderBuilderMock->expects($this->any())->method('setDescendingDirection')->willReturnSelf();
        $sortOrderBuilderMock->expects($this->once())->method('create')->willReturn($sortOrderMock);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock->expects($this->any())->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->once())->method('addSortOrder')->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $transactionRepositoryMock = $this->createMock(TransactionRepositoryInterface::class);
        $transactionMock = $this->createMock(TransactionInterface::class);
        $collectionMock = $this->createMock(Collection::class);
        $transactionRepositoryMock->expects($this->once())->method('getList')->willReturn($collectionMock);
        $collectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([
            $transactionMock,
        ]));

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['getIsTransactionApproved'])
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('update');
        $paymentMock->expects($this->any())->method('getIsTransactionApproved')->willReturn('1');

        $model = new UpdatePaymentByTransactions(
            $searchCriteriaBuilderMock,
            $sortOrderBuilderMock,
            $transactionRepositoryMock
        );

        $model->execute($paymentMock);
    }
}
