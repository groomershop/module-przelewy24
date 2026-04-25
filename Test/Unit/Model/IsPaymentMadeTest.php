<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\ApiClientInterfaceFactory;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PayPro\Przelewy24\Model\IsPaymentMade;
use PHPUnit\Framework\TestCase;

class IsPaymentMadeTest extends TestCase
{
    public function testExecute(): void
    {
        $config = [
            ApiConfig::URL => 'url',
            ApiConfig::USERNAME => 'username',
            ApiConfig::PASSWORD => 'password',
        ];

        $apiClientMock = $this->createMock(ApiClientInterface::class);
        $apiConfigMock = $this->createMock(ApiConfig::class);
        $apiClientFactoryMock = $this->createMock(ApiClientInterfaceFactory::class);
        $apiConfigMock->expects($this->once())->method('get')->willReturn($config);
        $apiClientFactoryMock->expects($this->once())->method('create')->with($config)->willReturn($apiClientMock);
        $apiClientMock->expects($this->once())->method('transactionStatus')->with('uuid')->willReturn([
            'data' => [
                ApiTransaction::STATUS => ApiTransaction::STATUS_ADVANCE_PAYMENT,
            ],
        ]);

        $model = new IsPaymentMade($apiConfigMock, $apiClientFactoryMock);
        $this->assertTrue($model->execute('uuid'));
    }
}
