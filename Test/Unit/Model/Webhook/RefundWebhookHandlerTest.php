<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionInterface;
use PayPro\Przelewy24\Api\TransactionRepositoryInterface;
use PayPro\Przelewy24\Model\Webhook\RefundWebhookHandler;

class RefundWebhookHandlerTest extends WebhookTestCase
{
    private const VALID_PAYLOAD = [
        'orderId' => 300000001,
        'sessionId' => 'test7',
        'merchantId' => 11111,
        'requestId' => 'request_id',
        'refundsUuid' => 'refunds_uuid',
        'amount' => 1,
        'currency' => 'PLN',
        'timestamp' => 1612349102,
        'status' => 0,
        'sign' => 'adac0d779e2fda2e2df27e58b57a535f6054ac0df1a33fbc125aad3883218bfb2b803e447a712d0ff0d6d2a0d719b840',
    ];

    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionMock;

    /**
     * @var \PayPro\Przelewy24\Api\TransactionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var \PayPro\Przelewy24\Model\Webhook\RefundWebhookHandler
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);
        $this->transactionRepositoryMock = $this->getMockForAbstractClass(TransactionRepositoryInterface::class);

        $this->model = new RefundWebhookHandler(
            $this->paymentLoggerMock,
            $this->configMock,
            $this->transactionRepositoryMock
        );
    }

    public function testHandle(): void
    {
        $this->transactionRepositoryMock->expects($this->once())->method('get')->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(false);
        $this->transactionMock->expects($this->once())->method('setIsClosed')->with(true);
        $this->transactionRepositoryMock->expects($this->once())->method('save');

        $this->model->handle(self::VALID_PAYLOAD);
    }

    public function testHandleInvalidSignature(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid signature for refund ' . self::VALID_PAYLOAD['refundsUuid']);
        $payload = self::VALID_PAYLOAD;
        $payload['sign'] = 'invalid signature';

        $this->transactionRepositoryMock->expects($this->never())->method('save');

        $this->model->handle($payload);
    }

    public function testHandleClosedTransaction(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Refund ' . self::VALID_PAYLOAD['refundsUuid'] . ' is already closed');

        $this->transactionRepositoryMock->expects($this->once())->method('get')->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getIsClosed')->willReturn(true);

        $this->model->handle(self::VALID_PAYLOAD);
    }
}
