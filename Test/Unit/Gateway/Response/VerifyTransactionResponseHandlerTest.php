<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use PayPro\Przelewy24\Gateway\Response\VerifyTransactionResponseHandler;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class VerifyTransactionResponseHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handlingSubject = ['data' => 1];
        $response = ['data' => 1];
        $rawDetails = ['transaction' => 'details'];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);

        $subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $paymentModelMock->expects($this->once())->method('getAuthorizationTransaction')->willReturn($transactionMock);
        $paymentModelMock->expects($this->once())->method('setIsTransactionPending')->with(false);
        $paymentModelMock->expects($this->once())->method('setIsTransactionClosed')->with(true);
        $paymentModelMock->expects($this->once())->method('setShouldCloseParentTransaction')->with(true);
        $transactionMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(Transaction::RAW_DETAILS)
            ->willReturn($rawDetails);
        $paymentModelMock->expects($this->once())
            ->method('setTransactionAdditionalInfo')
            ->with(Transaction::RAW_DETAILS, $rawDetails);

        $model = new VerifyTransactionResponseHandler($subjectReaderMock);
        $model->handle($handlingSubject, $response);
    }
}
