<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Webhook;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Model\Processor\InvoiceProcessor;
use PayPro\Przelewy24\Model\Processor\PaymentProcessor;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PayPro\Przelewy24\Model\Webhook\TransactionWebhookHandler;

class TransactionWebhookHandlerTest extends WebhookTestCase
{
    private const VALID_PAYLOAD = [
        'merchantId' => 11111,
        'posId' => 11111,
        'sessionId' => 'test7',
        'amount' => 1,
        'originAmount' => 1,
        'currency' => 'PLN',
        'orderId' => 000000001,
        'methodId' => 181,
        'statement' => 'p24-000-000-001',
        'sign' => 'f08ec5e02b7d1acbba0685241cf7723c4bb84e78a4c8e322f6e1389d46f5f9b0daa9e76336d9101abdac6ad221e728da',
    ];

    /**
     * @var \PayPro\Przelewy24\Model\Webhook\TransactionWebhookHandler
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\PaymentProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentProcessorMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\InvoiceProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceProcessorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->paymentProcessorMock = $this->createMock(PaymentProcessor::class);
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->invoiceProcessorMock = $this->createMock(InvoiceProcessor::class);

        $this->resourceConnectionMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->model = new TransactionWebhookHandler(
            $this->paymentLoggerMock,
            $this->configMock,
            $this->resourceConnectionMock,
            $this->paymentProcessorMock,
            $this->orderRepositoryMock,
            $this->invoiceProcessorMock
        );
    }

    public function testHandle(): void
    {
        $transaction = new ApiTransaction(self::VALID_PAYLOAD);

        $paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $this->paymentProcessorMock->expects($this->once())
            ->method('process')
            ->with($transaction)
            ->willReturn($paymentMock);
        $this->orderRepositoryMock->expects($this->once())->method('save')->with($orderMock);
        $this->invoiceProcessorMock->expects($this->once())->method('process')->with($paymentMock);
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollBack');

        $this->model->handle(self::VALID_PAYLOAD);
    }

    public function testHandleInvalidSignature(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid signature for transaction ' . self::VALID_PAYLOAD['sessionId']);
        $payload = self::VALID_PAYLOAD;
        $payload['sign'] = 'invalid signature';

        $this->paymentProcessorMock->expects($this->never())->method('process');
        $this->invoiceProcessorMock->expects($this->never())->method('process');
        $this->connectionMock->expects($this->never())->method('beginTransaction');
        $this->connectionMock->expects($this->never())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollBack');

        $this->model->handle($payload);
    }

    public function testHandleTransactionRollback(): void
    {
        $this->expectException(\Exception::class);
        $transaction = new ApiTransaction(self::VALID_PAYLOAD);

        $this->paymentProcessorMock->expects($this->once())
            ->method('process')
            ->with($transaction)
            ->willThrowException(new \Exception('Error'));
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->never())->method('commit');
        $this->connectionMock->expects($this->once())->method('rollBack');

        $this->model->handle(self::VALID_PAYLOAD);
    }
}
