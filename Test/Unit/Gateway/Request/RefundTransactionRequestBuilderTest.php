<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPro\Przelewy24\Api\SessionId\RefundsUuIdProviderInterface;
use PayPro\Przelewy24\Gateway\Request\RefundTransactionRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PHPUnit\Framework\TestCase;

class RefundTransactionRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $buildSubject = ['data' => 1];

        $transactionPayload = [
            ApiTransaction::MERCHANT_ID   => 11111,
            ApiTransaction::POS_ID        => 11111,
            ApiTransaction::SESSION_ID    => 'transaction_uuid',
            ApiTransaction::AMOUNT        => 1050,
            ApiTransaction::ORIGIN_AMOUNT => 1050,
            ApiTransaction::CURRENCY      => 'PLN',
            ApiTransaction::ORDER_ID      => 300000001,
            ApiTransaction::METHOD_ID     => 181,
            ApiTransaction::STATEMENT     => 'p24-000-000-001',
        ];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $refundsUuidProviderMock = $this->getMockForAbstractClass(RefundsUuIdProviderInterface::class);
        $identityGeneratorMock = $this->getMockForAbstractClass(IdentityGeneratorInterface::class);
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($paymentMock);
        $subjectReaderMock->expects($this->once())->method('readOrderStoreId')->with($buildSubject)->willReturn(1);
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $subjectReaderMock->expects($this->once())->method('readAmount')->willReturn(10.50);
        $subjectReaderMock->expects($this->once())->method('readOrderIncrementId')->willReturn('00000001');
        $paymentModelMock->expects($this->once())->method('getAuthorizationTransaction')->willReturn($transactionMock);
        $transactionMock->expects($this->once())->method('getAdditionalInformation')->willReturn($transactionPayload);
        $storeManagerMock->expects($this->once())->method('getStore')->with(1)->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn('https://baseurl/');
        $identityGeneratorMock->expects($this->once())
            ->method('generateId')
            ->willReturn('uuid');
        $refundsUuidProviderMock->expects($this->once())->method('get')->willReturn('uuid');

        $model = new RefundTransactionRequestBuilder(
            $subjectReaderMock,
            $identityGeneratorMock,
            $storeManagerMock,
            $refundsUuidProviderMock
        );

        $this->assertEquals([
            'requestId' => 'uuid',
            'refunds' => [
                [
                    'orderId' => 300000001,
                    'sessionId' => 'transaction_uuid',
                    'amount' => 1050,
                    'description' => 'Order: #00000001',
                ],
            ],
            'refundsUuid' => 'uuid',
            'urlStatus' => 'https://baseurl/przelewy24/status/refund',
        ], $model->build($buildSubject));
    }
}
