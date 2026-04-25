<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\ErrorFormatter;
use PHPUnit\Framework\TestCase;

class ErrorFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $orderMock = $this->createMock(Order::class);
        $paymentMock = $this->createMock(Payment::class);
        $paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $orderMock->expects($this->any())->method('getIncrementId')->willReturn('000000001');
        $paymentMock->expects($this->any())->method('getOrder')->willReturn($orderMock);
        $paymentMock->expects($this->any())->method('getTransactionId')->willReturn('uuid');
        $paymentDOMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $errorFormatter = new ErrorFormatter(new SubjectReader());

        $this->assertEquals(
            __('Przelewy24 error (%1: %2): %3', $orderMock->getIncrementId(), $paymentMock->getTransactionId(), 'err'),
            $errorFormatter->format('err', ['payment' => $paymentDOMock])
        );
    }
}
