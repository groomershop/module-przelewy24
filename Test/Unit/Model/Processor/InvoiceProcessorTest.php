<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Processor;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Model\Processor\InvoiceProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InvoiceProcessorTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender|\PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceSenderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceMock;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\InvoiceProcessor
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceSenderMock = $this->createMock(InvoiceSender::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['getCreatedInvoice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->createMock(Invoice::class);

        $this->model = new InvoiceProcessor($this->invoiceSenderMock, $this->loggerMock);
    }

    public function testProcess(): void
    {
        $this->paymentMock->expects($this->once())->method('getCreatedInvoice')->willReturn($this->invoiceMock);
        $this->invoiceSenderMock->expects($this->once())->method('send')->with($this->invoiceMock);
        $this->loggerMock->expects($this->never())->method('error');
        $this->model->process($this->paymentMock);
    }

    public function testProcessNoInvoiceCreated(): void
    {
        $this->paymentMock->expects($this->once())->method('getCreatedInvoice')->willReturn(null);
        $this->invoiceSenderMock->expects($this->never())->method('send');
        $this->loggerMock->expects($this->never())->method('error');
        $this->model->process($this->paymentMock);
    }

    public function testInvoiceSenderException(): void
    {
        $this->paymentMock->expects($this->once())->method('getCreatedInvoice')->willReturn($this->invoiceMock);
        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($this->invoiceMock)
            ->willThrowException(new \Exception('error'));
        $this->loggerMock->expects($this->once())->method('error');

        $this->model->process($this->paymentMock);
    }
}
