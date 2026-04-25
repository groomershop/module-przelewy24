<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use PayPro\Przelewy24\Gateway\Http\Client\VerifyTransactionClient;

class VerifyTransactionClientTest extends ClientTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new VerifyTransactionClient(
            $this->apiClientFactoryMock,
            $this->loggerMock,
            $this->paymentLoggerMock
        );
    }

    public function testPlaceRequest(): void
    {
        $response = ['data' => 1];

        $this->apiClientMock->expects($this->once())
            ->method('verifyTransaction')
            ->with(self::TRANSFER_DATA)
            ->willReturn($response);

        $this->assertEquals($response, $this->model->placeRequest($this->transferMock));
    }

    public function testPlaceRequestException(): void
    {
        $this->expectException(ClientException::class);
        $this->apiClientMock->expects($this->once())
            ->method('verifyTransaction')
            ->willThrowException(new \Exception('Error'));

        $this->loggerMock->expects($this->once())->method('critical');

        $this->model->placeRequest($this->transferMock);
    }
}
