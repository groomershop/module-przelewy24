<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Response\CardVaultResponseHandler;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class CardVaultResponseHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $paymentMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $paymentModelMock->expects($this->once())->method('setIsTransactionPending')->with(true);
        $paymentModelMock->expects($this->once())->method('setIsTransactionClosed')->with(false);

        $model = new CardVaultResponseHandler(new SubjectReader());
        $model->handle(['payment' => $paymentMock], ['data' => []]);
    }
}
