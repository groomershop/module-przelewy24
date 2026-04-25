<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use PayPro\Przelewy24\Gateway\Request\BillingAddressRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class BillingAddressRequestBuilderTest extends TestCase
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Request\BillingAddressRequestBuilder
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new BillingAddressRequestBuilder(new SubjectReader());
    }

    public function testBuild(): void
    {
        $paymentMock =  $this->createMock(PaymentDataObjectInterface::class);
        $orderMock = $this->createMock(OrderAdapterInterface::class);
        $billingAddressMock = $this->createMock(AddressAdapterInterface::class);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('getEmail')->willReturn('test@example.com');
        $billingAddressMock->expects($this->once())->method('getFirstname')->willReturn('John');
        $billingAddressMock->expects($this->once())->method('getLastname')->willReturn('Doe');
        $billingAddressMock->expects($this->once())->method('getStreetLine1')->willReturn('Street name');
        $billingAddressMock->expects($this->once())->method('getStreetLine2')->willReturn('11');
        $billingAddressMock->expects($this->once())->method('getPostcode')->willReturn('00-001');
        $billingAddressMock->expects($this->once())->method('getCity')->willReturn('Warsaw');
        $billingAddressMock->expects($this->once())->method('getCountryId')->willReturn('PL');

        $buildSubject = ['payment' => $paymentMock];

        $this->assertEquals([
            'email' => 'test@example.com',
            'client' => 'John Doe',
            'address' => 'Street name 11',
            'zip' => '00-001',
            'city' => 'Warsaw',
            'country' => 'PL',
        ], $this->model->build($buildSubject));
    }

    public function testBuildOrderAddressModel(): void
    {
        $paymentMock =  $this->createMock(PaymentDataObjectInterface::class);
        $orderMock = $this->createMock(OrderAdapterInterface::class);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $billingAddressMock = $this->createMock(OrderAddressInterface::class);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('getEmail')->willReturn('test@example.com');
        $billingAddressMock->expects($this->once())->method('getFirstname')->willReturn('John');
        $billingAddressMock->expects($this->once())->method('getLastname')->willReturn('Doe');
        $billingAddressMock->expects($this->once())->method('getPostcode')->willReturn('00-001');
        $billingAddressMock->expects($this->once())->method('getCity')->willReturn('Warsaw');
        $billingAddressMock->expects($this->once())->method('getCountryId')->willReturn('PL');
        $billingAddressMock->expects($this->once())->method('getStreet')->willReturn(['Street name', '11']);

        $buildSubject = ['payment' => $paymentMock];

        $this->assertEquals([
            'email' => 'test@example.com',
            'client' => 'John Doe',
            'address' => 'Street name 11',
            'zip' => '00-001',
            'city' => 'Warsaw',
            'country' => 'PL',
        ], $this->model->build($buildSubject));
    }

    public function testBuildNoAddress(): void
    {
        $this->expectException(CommandException::class);
        $paymentMock =  $this->createMock(PaymentDataObjectInterface::class);
        $orderMock = $this->createMock(OrderAdapterInterface::class);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn(null);

        $buildSubject = ['payment' => $paymentMock];
        $this->model->build($buildSubject);
    }
}
