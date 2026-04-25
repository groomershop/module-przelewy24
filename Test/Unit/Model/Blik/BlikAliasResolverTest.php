<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Blik;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPro\Przelewy24\Api\Data\BlikAliasInterface;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\Blik\BlikAliasResolver;
use PHPUnit\Framework\TestCase;

class BlikAliasResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $paymentMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $orderMock = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $addressMock = $this->getMockForAbstractClass(AddressAdapterInterface::class);
        $addressMock->expects($this->once())->method('getEmail')->willReturn('email@example.com');
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($addressMock);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $paymentMock->expects($this->exactly(2))->method('getOrder')->willReturn($orderMock);
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getBaseUrl'])
            ->getMockForAbstractClass();
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn('https://example.com/');
        $storeManagerMock->expects($this->once())->method('getStore')->with(1)->willReturn($storeMock);

        $model = new BlikAliasResolver(new SubjectReader(), $storeManagerMock, new \Laminas\Uri\Uri());
        $alias = $model->resolve([
            'payment' => $paymentMock,
        ]);

        $this->assertInstanceOf(BlikAliasInterface::class, $alias);
        $this->assertEquals('email@example.com', $alias->getValue());
        $this->assertEquals('example.com', $alias->getLabel());
    }
}
