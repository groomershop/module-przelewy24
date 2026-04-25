<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Model\PayAgain;
use PayPro\Przelewy24\Model\PaymentLink;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PayPro\Przelewy24\Observer\GatewayDataAssignObserver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PayAgainTest extends TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \PayPro\Przelewy24\Model\PayAgain
     */
    private $model;

    protected function setUp(): void
    {
        $resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->paymentMock = $this->createMock(Payment::class);
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($this->orderMock);

        $this->model = new PayAgain(
            $resourceConnectionMock,
            $this->orderRepositoryMock,
            $this->loggerMock
        );
    }

    public function testExecute(): void
    {
        $this->paymentMock->expects($this->once())
            ->method('unsAdditionalInformation')
            ->with(GatewayDataAssignObserver::METHOD);
        $this->paymentMock->expects($this->once())->method('setMethod')->with(ConfigProvider::CODE);
        $this->paymentMock->expects($this->once())
            ->method('setData')
            ->with(TransactionPayloadInterface::PAYMENT_RETURN_ROUTE, PaymentLink::SUCCESS_ROUTE);
        $this->paymentMock->expects($this->once())->method('setParentTransactionId')->with('uuid');
        $this->paymentMock->expects($this->once())->method('place');
        $this->orderMock->expects($this->once())->method('setPayment')->with($this->paymentMock);
        $this->orderRepositoryMock->expects($this->once())->method('save')->with($this->orderMock);
        $this->connectionMock->expects($this->once())->method('commit');

        $this->model->execute('uuid', $this->paymentMock, []);
    }

    public function testExecuteWithError(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Can\'t process new payment');
        $this->connectionMock->expects($this->once())->method('rollback');
        $this->loggerMock->expects($this->once())->method('error');
        $this->paymentMock->expects($this->once())
            ->method('place')
            ->willThrowException(new \Exception('Error'));

        $this->model->execute('uuid', $this->paymentMock, []);
    }
}
