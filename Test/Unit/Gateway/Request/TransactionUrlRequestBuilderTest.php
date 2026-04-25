<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPro\Przelewy24\Gateway\Request\TransactionUrlRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class TransactionUrlRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $storeId = 1;

        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getUrl'])
            ->getMockForAbstractClass();
        $storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);
        $storeMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls('return_url', 'status_url');

        $orderAdapterMock = $this->createMock(OrderAdapterInterface::class);
        $orderAdapterMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $paymentMock = $this->createMock(Payment::class);
        $paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDataObjectMock->expects($this->once())->method('getOrder')->willReturn($orderAdapterMock);
        $paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);

        $model = new TransactionUrlRequestBuilder(new SubjectReader(), $storeManagerMock);
        $this->assertEquals([
            'urlReturn' => 'return_url',
            'urlStatus' => 'status_url',
        ], $model->build([
            'payment' => $paymentDataObjectMock,
        ]));
    }
}
