<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Response\RefundTransactionResponseHandler;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class RefundTransactionResponseHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();

        $subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);

        $paymentModelMock->expects($this->once())->method('setIsTransactionPending')->with(true);
        $paymentModelMock->expects($this->once())->method('setIsTransactionClosed')->with(false);

        $model = new RefundTransactionResponseHandler($subjectReaderMock);

        $model->handle([], []);
    }
}
