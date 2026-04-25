<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PayPro\Przelewy24\Model\TransactionRepository;
use PHPUnit\Framework\TestCase;

class TransactionRepositoryTest extends TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionCollectionMock;

    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionMock;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var \PayPro\Przelewy24\Model\TransactionRepository
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->transactionCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);

        $this->transactionRepositoryMock = $this->getMockForAbstractClass(TransactionRepositoryInterface::class);

        $this->model = new TransactionRepository($this->searchCriteriaBuilderMock, $this->transactionRepositoryMock);
    }

    public function testGet(): void
    {
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')
            ->with(TransactionInterface::TXN_ID, 'p24_transaction_id')
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->transactionCollectionMock);

        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->transactionCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->transactionMock);

        $transaction = new ApiTransaction([
            ApiTransaction::SESSION_ID => 'p24_transaction_id',
        ]);
        $this->assertEquals($this->transactionMock, $this->model->get($transaction));
    }

    public function testNotFound(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Transaction ' . 'p24_nonexistent_transaction_id' . ' not found');

        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')
            ->with(TransactionInterface::TXN_ID, 'p24_nonexistent_transaction_id')
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->transactionCollectionMock);

        $this->transactionCollectionMock->expects($this->once())->method('count')->willReturn(0);

        $transaction = new ApiTransaction([
            ApiTransaction::SESSION_ID => 'p24_nonexistent_transaction_id',
        ]);
        $this->model->get($transaction);
    }

    public function testSave(): void
    {
        $transactionPayload = [
            ApiTransaction::MERCHANT_ID   => 11111,
            ApiTransaction::POS_ID        => 11111,
            ApiTransaction::SESSION_ID    => 'test7',
            ApiTransaction::AMOUNT        => 1,
            ApiTransaction::ORIGIN_AMOUNT => 1,
            ApiTransaction::CURRENCY      => 'PLN',
            ApiTransaction::ORDER_ID      => 000000001,
            ApiTransaction::METHOD_ID     => 181,
            ApiTransaction::STATEMENT     => 'p24-000-000-001',
        ];
        $transaction = new ApiTransaction($transactionPayload);

        $this->transactionMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(Transaction::RAW_DETAILS, $transactionPayload);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->transactionMock);

        $this->model->save($transaction, $this->transactionMock);
    }
}
