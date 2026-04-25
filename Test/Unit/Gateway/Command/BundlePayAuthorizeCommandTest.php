<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Command\BundlePayAuthorizeCommand;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Observer\TokenDataAssignObserver;
use PHPUnit\Framework\TestCase;

class BundlePayAuthorizeCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $paymentModelMock->expects($this->once())->method('setTransactionId')->with('uuid');
        $paymentModelMock->expects($this->once())->method('setIsTransactionPending')->with(true);
        $paymentModelMock->expects($this->once())->method('setIsTransactionClosed')->with(false);
        $paymentModelMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(TokenDataAssignObserver::SESSION_ID)
            ->willReturn('uuid');
        $paymentMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);

        $model = new BundlePayAuthorizeCommand(new SubjectReader());
        $model->execute(['payment' => $paymentMock]);
    }
}
