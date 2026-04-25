<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPro\Przelewy24\Model\PaymentLink;
use PHPUnit\Framework\TestCase;

class PaymentLinkTest extends TestCase
{
    public function testExecute(): void
    {
        $url = 'https://magento.test/przelewy24/payment/pay';

        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getUrl'])
            ->getMockForAbstractClass();
        $storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getUrl')->willReturn($url);

        $model = new PaymentLink($storeManagerMock);

        $this->assertEquals($url, $model->execute($paymentMock));
    }
}
