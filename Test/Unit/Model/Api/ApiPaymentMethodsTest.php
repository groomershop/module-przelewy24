<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\ApiClientInterfaceFactory;
use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterfaceFactory;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethods;
use PHPUnit\Framework\TestCase;

class ApiPaymentMethodsTest extends TestCase
{
    private const API_CONFIG = [
        'url' => 'https://sandbox.przelewy24.pl',
        'username' => 'username',
        'password' => 'password',
    ];

    public function testExecute(): void
    {
        $methods = [
            ['id' => '1', 'name' => 'payment1'],
            ['id' => '2', 'name' => 'payment2'],
        ];

        $apiClientFactoryMock = $this->getMockBuilder(ApiClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiPaymentMethodFactoryMock = $this->getMockBuilder(ApiPaymentMethodInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)->disableOriginalConstructor()->getMock();
        $apiClientMock = $this->getMockForAbstractClass(ApiClientInterface::class);
        $apiClientFactoryMock->expects($this->once())
            ->method('create')
            ->with(self::API_CONFIG)
            ->willReturn($apiClientMock);

        $apiClientMock->expects($this->once())->method('paymentMethods')->with('en')->willReturn($methods);
        $apiPaymentMethodFactoryMock->expects($this->exactly(count($methods)))
            ->method('create')
            ->willReturn(new DataObject(['id' => 'id', 'name' => 'payment']));
        $dataObjectHelperMock->expects($this->exactly(count($methods)))->method('populateWithArray');

        $model = new ApiPaymentMethods(
            $apiClientFactoryMock,
            $apiPaymentMethodFactoryMock,
            $dataObjectHelperMock
        );

        $this->assertEquals([
            new DataObject(['id' => 'id', 'name' => 'payment']),
            new DataObject(['id' => 'id', 'name' => 'payment']),
        ], $model->execute('en', self::API_CONFIG));
    }
}
