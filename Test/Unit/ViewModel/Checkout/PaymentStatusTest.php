<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\ViewModel\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\ApiClientInterfaceFactory;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PayPro\Przelewy24\ViewModel\Checkout\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentStatusTest extends TestCase
{
    private const API_CREDENTIALS = ['username' => 'user', 'password' => 'pass'];

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiConfigMock;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClientFactoryMock;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClientMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionMock;

    /**
     * @var \PayPro\Przelewy24\ViewModel\Checkout\PaymentStatus
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiConfigMock = $this->getMockBuilder(ApiConfig::class)->disableOriginalConstructor()->getMock();
        $this->apiClientFactoryMock = $this->getMockBuilder(ApiClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiClientMock = $this->getMockForAbstractClass(ApiClientInterface::class);
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $this->transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);

        $this->model = new PaymentStatus(
            $this->apiConfigMock,
            $this->apiClientFactoryMock,
            $this->checkoutSessionMock
        );
    }

    public function testIsVisible(): void
    {
        $this->checkoutSessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');

        $this->assertTrue($this->model->isVisible());
    }

    public function testIsTransactionPaid(): void
    {
        $this->checkoutSessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->transactionMock->expects($this->once())->method('getTxnId')->willReturn('transaction_id');
        $this->paymentMock->expects($this->once())
            ->method('getAuthorizationTransaction')
            ->willReturn($this->transactionMock);
        $this->apiConfigMock->expects($this->once())->method('get')->willReturn(self::API_CREDENTIALS);
        $this->apiClientFactoryMock->expects($this->once())
            ->method('create')
            ->with(self::API_CREDENTIALS)
            ->willReturn($this->apiClientMock);
        $this->apiClientMock->expects($this->once())->method('transactionStatus')->with('transaction_id')->willReturn([
            'data' => [
                'status' => 2,
            ],
        ]);

        $this->assertTrue($this->model->isTransactionPaid());
    }

    public function testIsTransactionPaidWithoutTransaction(): void
    {
        $this->checkoutSessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getAuthorizationTransaction')->willReturn(false);

        $this->assertFalse($this->model->isTransactionPaid());
    }

    public function testIsTransactionPaidException(): void
    {
        $this->checkoutSessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())
            ->method('getAuthorizationTransaction')
            ->willReturn($this->transactionMock);
        $this->transactionMock->expects($this->once())->method('getTxnId')->willReturn('transaction_id');
        $this->apiConfigMock->expects($this->once())->method('get')->willReturn(self::API_CREDENTIALS);
        $this->apiClientFactoryMock->expects($this->once())
            ->method('create')
            ->with(self::API_CREDENTIALS)
            ->willReturn($this->apiClientMock);
        $this->apiClientMock->expects($this->once())
            ->method('transactionStatus')
            ->with('transaction_id')
            ->willThrowException(new \Exception());

        $this->assertFalse($this->model->isTransactionPaid());
    }
}
