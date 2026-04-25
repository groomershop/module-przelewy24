<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use PayPro\Przelewy24\Gateway\Http\Client\BlikChargeByAliasClient;

class BlikChargeByAliasClientTest extends ClientTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new BlikChargeByAliasClient(
            $this->apiClientFactoryMock,
            $this->loggerMock,
            $this->paymentLoggerMock
        );
    }

    public function testPlaceRequest(): void
    {
        $response = [
            'data' => [
                'orderId' => 3000001,
            ],
            'sessionId' => self::TRANSFER_DATA['sessionId'],
        ];

        $this->apiClientMock->expects($this->once())
            ->method('registerTransaction')
            ->with(self::TRANSFER_DATA)
            ->willReturn(['data' => ['token' => 'uuid']]);

        $this->apiClientMock->expects($this->once())
            ->method('blikChargeByAlias')
            ->with(['token' => 'uuid', 'type' => 'alias'])
            ->willReturn($response);

        $this->assertEquals($response, $this->model->placeRequest($this->transferMock));
    }

    public function testEmptyToken(): void
    {
        $this->expectException(ClientException::class);
        $this->apiClientMock->expects($this->once())
            ->method('registerTransaction')
            ->with(self::TRANSFER_DATA)
            ->willReturn(['data' => []]);

        $this->loggerMock->expects($this->once())->method('critical');

        $this->model->placeRequest($this->transferMock);
    }

    public function testPlaceRequestException(): void
    {
        $this->expectException(ClientException::class);
        $this->apiClientMock->expects($this->once())
            ->method('registerTransaction')
            ->willThrowException(new \Exception('Error'));

        $this->loggerMock->expects($this->once())->method('critical');

        $this->model->placeRequest($this->transferMock);
    }
}
