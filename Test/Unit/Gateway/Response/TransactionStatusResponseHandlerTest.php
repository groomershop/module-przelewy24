<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Gateway\Response\TransactionStatusResponseHandler;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\DenyExpiredPayment;
use PayPro\Przelewy24\Model\Processor\PaymentProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TransactionStatusResponseHandlerTest extends TestCase
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\PaymentProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentProcessorMock;

    /**
     * @var \PayPro\Przelewy24\Model\DenyExpiredPayment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $denyExpiredPaymentMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Payment\Gateway\Data\OrderAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderAdapterMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Response\TransactionStatusResponseHandler
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectReaderMock = $this->createMock(SubjectReader::class);
        $this->configMock = $this->createMock(CommonConfig::class);
        $this->paymentProcessorMock = $this->createMock(PaymentProcessor::class);
        $this->denyExpiredPaymentMock = $this->createMock(DenyExpiredPayment::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->orderAdapterMock = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $this->subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($this->orderAdapterMock);
        $this->orderAdapterMock->expects($this->once())->method('getStoreId')->willReturn('1');
        $this->configMock->expects($this->once())->method('getMerchantId')->with(1)->willReturn(111);
        $this->configMock->expects($this->once())->method('getPosId')->with(1)->willReturn(111);

        $this->model = new TransactionStatusResponseHandler(
            $this->subjectReaderMock,
            $this->configMock,
            $this->paymentProcessorMock,
            $this->denyExpiredPaymentMock,
            $this->loggerMock
        );
    }

    public function testHandle(): void
    {
        $paymentModelMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setIsTransactionApproved'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $paymentModelMock->expects($this->once())->method('setIsTransactionApproved')->with(true);
        $this->paymentProcessorMock->expects($this->once())->method('process');
        $this->denyExpiredPaymentMock->expects($this->never())->method('execute');

        $this->model->handle([], [
            'data' => [
                'paymentMethod' => 181,
                'amount' => 100,
                'status' => 1,
            ],
        ]);
    }

    public function testHandlePaymentProcessorFailure(): void
    {
        $this->expectException(CommandException::class);
        $this->loggerMock->expects($this->once())->method('error');
        $this->paymentProcessorMock->expects($this->once())
            ->method('process')
            ->willThrowException(new LocalizedException(__('error')));
        $this->denyExpiredPaymentMock->expects($this->never())->method('execute');
        $this->paymentMock->expects($this->never())->method('getPayment');

        $this->model->handle([], [
            'data' => [
                'paymentMethod' => 181,
                'amount' => 100,
                'status' => 1,
            ],
        ]);
    }

    public function testHandleDenyPayment(): void
    {
        $this->paymentMock->expects($this->never())->method('getPayment');
        $this->paymentProcessorMock->expects($this->never())->method('process');
        $this->denyExpiredPaymentMock->expects($this->once())->method('execute');

        $this->model->handle([], [
            'data' => [
                'paymentMethod' => 181,
                'amount' => 100,
                'status' => 0,
            ],
        ]);
    }
}
