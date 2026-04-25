<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterfaceFactory;
use PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Gateway\Request\RegisterTransactionRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\TransactionPayload;
use PHPUnit\Framework\TestCase;

class RegisterTransactionRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $buildSubject = ['data' => 1];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $commonConfigMock = $this->getMockBuilder(CommonConfig::class)->disableOriginalConstructor()->getMock();
        $gatewayConfigMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();
        $sessionIdProviderMock = $this->getMockForAbstractClass(SessionIdProviderInterface::class);
        $transactionPayloadFactoryMock = $this->getMockBuilder(TransactionPayloadInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();

        $subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $subjectReaderMock->expects($this->once())->method('readOrderIncrementId')->willReturn('00000001');
        $sessionIdProviderMock->expects($this->once())->method('get')->willReturn('uuid');
        $paymentModelMock->expects($this->once())->method('setTransactionId')->with('uuid');
        $paymentModelMock->expects($this->once())->method('getMethod')->willReturn('method');
        $paymentModelMock->expects($this->exactly(2))->method('getAdditionalInformation')->willReturn(null);
        $subjectReaderMock->expects($this->once())->method('readCurrency')->willReturn('PLN');
        $subjectReaderMock->expects($this->once())->method('readAmount')->willReturn(10.50);
        $subjectReaderMock->expects($this->once())->method('readOrderStoreId')->willReturn(1);
        $commonConfigMock->expects($this->once())->method('getMerchantId')->willReturn(11111);
        $commonConfigMock->expects($this->once())->method('getPosId')->willReturn(11111);

        $transactionPayload = new TransactionPayload([
            'merchantId' => 11111,
            'posId' => 11111,
            'sessionId' => 'uuid',
            'amount' => 10.50,
            'currency' => 'PLN',
            'description' => 'Order: #00000001',
            'waitForResult' => false,
            'encoding' => ApiClientInterface::ENCODING,
        ]);
        $transactionPayloadFactoryMock->expects($this->once())->method('create')->willReturn($transactionPayload);

        $model = new RegisterTransactionRequestBuilder(
            $subjectReaderMock,
            $commonConfigMock,
            $gatewayConfigMock,
            $transactionPayloadFactoryMock,
            $sessionIdProviderMock
        );

        $signature = 'b3de0702a7a76405d3279ce8eaf366197b0918ca1193157cc50f05ebcbbe8b8e777d7080714737867409df544575cd61';

        $this->assertEquals([
            'merchantId' => 11111,
            'posId' => 11111,
            'sessionId' => 'uuid',
            'amount' => 1050,
            'currency' => 'PLN',
            'description' => 'Order: #00000001',
            'waitForResult' => false,
            'sign' => $signature,
            'encoding' => ApiClientInterface::ENCODING,
        ], $model->build($buildSubject));
    }
}
