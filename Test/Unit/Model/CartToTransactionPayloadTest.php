<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterfaceFactory;
use PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Model\TransactionPayloadTransformer;
use PayPro\Przelewy24\Model\LanguageResolver;
use PHPUnit\Framework\TestCase;

class CartToTransactionPayloadTest extends TestCase
{
    public function testExecute(): void
    {
        $transactionPayloadFactoryMock = $this->getMockBuilder(TransactionPayloadInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commonConfigMock = $this->getMockBuilder(CommonConfig::class)->disableOriginalConstructor()->getMock();
        $commonConfigMock->expects($this->once())->method('getMerchantId')->with('1')->willReturn(11111);
        $commonConfigMock->expects($this->once())->method('getPosId')->with('1')->willReturn(11111);
        $commonConfigMock->expects($this->once())->method('getCrcKey')->with('1')->willReturn('crc_key');
        $sessionIdProviderMock = $this->getMockForAbstractClass(SessionIdProviderInterface::class);
        $sessionIdProviderMock->expects($this->once())->method('get')->willReturn('uuid');
        $languageResolverMock = $this->getMockBuilder(LanguageResolver:: class)
            ->disableOriginalConstructor()
            ->getMock();
        $languageResolverMock->expects($this->once())->method('resolve')->with('1')->willReturn('pl');
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getUrl'])
            ->getMockForAbstractClass();
        $storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->exactly(2))->method('getUrl')->withConsecutive(
            [TransactionPayloadInterface::RETURN_ROUTE],
            [TransactionPayloadInterface::STATUS_ROUTE],
        )->willReturnOnConsecutiveCalls('url1', 'url2');
        $quoteMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $addressMock = $this->getMockBuilder(AddressInterface::class)->getMockForAbstractClass();
        $paymentMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn('method');
        $paymentMock->expects($this->once())->method('getAdditionalInformation')->willReturn([]);
        $quoteMock->expects($this->once())->method('reserveOrderId');
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn('1');
        $quoteMock->expects($this->once())->method('getReservedOrderId')->willReturn('000000001');
        $quoteMock->expects($this->exactly(2))->method('getPayment')->willReturn($paymentMock);
        $addressMock->expects($this->once())->method('getFirstname')->willReturn('firstname');
        $addressMock->expects($this->once())->method('getLastname')->willReturn('lastname');
        $addressMock->expects($this->exactly(2))->method('getStreet')->willReturn(['street1', 'street2']);
        $addressMock->expects($this->once())->method('getEmail')->willReturn('email');
        $addressMock->expects($this->once())->method('getCity')->willReturn('city');
        $addressMock->expects($this->once())->method('getPostcode')->willReturn('postcode');
        $addressMock->expects($this->once())->method('getCountryId')->willReturn('country_id');
        $transactionPayloadMock = $this->getMockForAbstractClass(TransactionPayloadInterface::class);
        $transactionPayloadMock->expects($this->once())->method('get')->with('crc_key')->willReturn([
            'transaction' => 'payload',
        ]);
        $transactionPayloadFactoryMock->expects($this->once())->method('create')->with(['data' => [
            'merchantId' => 11111,
            'posId' => 11111,
            'sessionId' => 'uuid',
            'amount' => null,
            'currency' => null,
            'description' => 'Order: #000000001',
            'encoding' => 'UTF-8',
            'email' => 'email',
            'client' => 'firstname lastname',
            'address' => 'street1 street2',
            'zip' => 'postcode',
            'city' => 'city',
            'country' => 'country_id',
            'language' => 'pl',
            'urlReturn' => 'url1',
            'urlStatus' => 'url2',
        ]])->willReturn($transactionPayloadMock);

        $model = new TransactionPayloadTransformer(
            $transactionPayloadFactoryMock,
            $commonConfigMock,
            $languageResolverMock,
            $storeManagerMock,
            $sessionIdProviderMock
        );

        $this->assertEquals([
            'transaction' => 'payload',
        ], $model->fromCart($quoteMock, []));
    }
}
