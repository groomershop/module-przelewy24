<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Gateway\Request\VerifyTransactionRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PHPUnit\Framework\TestCase;

class VerifyTransactionRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $buildSubject = ['data' => 1];

        $transactionPayload = [
            ApiTransaction::MERCHANT_ID   => 11111,
            ApiTransaction::POS_ID        => 11111,
            ApiTransaction::SESSION_ID    => 'uuid',
            ApiTransaction::AMOUNT        => 1050,
            ApiTransaction::ORIGIN_AMOUNT => 1050,
            ApiTransaction::CURRENCY      => 'PLN',
            ApiTransaction::ORDER_ID      => 300000001,
            ApiTransaction::METHOD_ID     => 181,
            ApiTransaction::STATEMENT     => 'p24-000-000-001',
        ];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $configMock = $this->getMockBuilder(CommonConfig::class)->disableOriginalConstructor()->getMock();
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);

        $subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $subjectReaderMock->expects($this->once())->method('readCurrency')->willReturn('PLN');
        $subjectReaderMock->expects($this->once())->method('readAmount')->willReturn(10.50);
        $paymentModelMock->expects($this->once())->method('getAuthorizationTransaction')->willReturn($transactionMock);
        $transactionMock->expects($this->once())->method('getAdditionalInformation')->willReturn($transactionPayload);
        $configMock->expects($this->once())->method('getMerchantId')->willReturn(11111);
        $configMock->expects($this->once())->method('getPosId')->willReturn(11111);

        $model = new VerifyTransactionRequestBuilder($subjectReaderMock, $configMock);
        $signature = '1311cbc0731ea76a5b68fe9b0e6713993550267155f3038f45bb70909c3f279490e21d3fd5704d6765e056f7482af412';
        $this->assertEquals([
            'merchantId' => 11111,
            'posId' => 11111,
            'sessionId' => 'uuid',
            'amount' => 1050,
            'currency' => 'PLN',
            'orderId' => 300000001,
            'sign' => $signature,
        ], $model->build($buildSubject));
    }
}
