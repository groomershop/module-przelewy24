<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Request\AdditionalInformationRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Observer\GatewayDataAssignObserver;
use PHPUnit\Framework\TestCase;

class AdditionalInformationRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $subject = [
            'payment' => 'data',
        ];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $subjectReaderMock->expects($this->once())->method('readPayment')->with($subject)->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $paymentModelMock->expects($this->exactly(2))
            ->method('getAdditionalInformation')
            ->withConsecutive(
                [GatewayDataAssignObserver::REGULATION_ACCEPT],
                [GatewayDataAssignObserver::METHOD]
            )->willReturnOnConsecutiveCalls('1', '180');

        $model = new AdditionalInformationRequestBuilder($subjectReaderMock);

        $this->assertEquals([
            'regulationAccept' => true,
            'method' => 180,
        ], $model->build($subject));
    }
}
