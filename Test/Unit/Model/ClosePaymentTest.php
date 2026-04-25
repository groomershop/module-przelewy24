<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PayPro\Przelewy24\Model\ClosePayment;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClosePaymentTest extends TestCase
{
    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\ClosePayment
     */
    private $model;

    protected function setUp(): void
    {
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->any())->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn(new SearchCriteria());
        $this->transactionRepositoryMock = $this->createMock(TransactionRepositoryInterface::class);
        $this->transactionMock = $this->createMock(TransactionInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->paymentMock = $this->createMock(OrderPaymentInterface::class);
        $this->transactionRepositoryMock->expects($this->once())->method('getList')->willReturn($this->collectionMock);

        $this->model = new ClosePayment(
            $searchCriteriaBuilderMock,
            $this->transactionRepositoryMock,
            $this->loggerMock
        );
    }

    public function testExecute(): void
    {
        $this->collectionMock->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([
            $this->transactionMock,
        ]));
        $this->transactionRepositoryMock->expects($this->once())->method('save');
        $this->loggerMock->expects($this->never())->method('error');

        $this->model->execute($this->paymentMock);
    }

    public function testExecuteNoTransactions(): void
    {
        $this->collectionMock->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([]));
        $this->transactionRepositoryMock->expects($this->never())->method('save');

        $this->model->execute($this->paymentMock);
    }

    public function testExecuteError(): void
    {
        $this->collectionMock->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([
            $this->transactionMock,
        ]));
        $this->transactionRepositoryMock->expects($this->once())->method('save')->willThrowException(
            new \Exception('Error')
        );
        $this->loggerMock->expects($this->once())->method('error');

        $this->model->execute($this->paymentMock);
    }
}
