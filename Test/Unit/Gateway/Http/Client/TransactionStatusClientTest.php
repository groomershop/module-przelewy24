<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use PayPro\Przelewy24\Gateway\Http\Client\TransactionStatusClient;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class TransactionStatusClientTest extends ClientTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new TransactionStatusClient(
            $this->apiClientFactoryMock,
            $this->loggerMock,
            $this->paymentLoggerMock
        );
    }

    public function testPlaceRequest(): void
    {
        $response = ['data' => 1];

        $this->apiClientMock->expects($this->once())
            ->method('transactionStatus')
            ->with(self::TRANSFER_DATA['sessionId'])
            ->willReturn($response);

        $this->assertEquals($response, $this->model->placeRequest($this->transferMock));
    }

    public function testPlaceRequestTransactionNotFound(): void
    {
        $response = [
            'error' => 'Transaction not found',
            'responseCode' => 0,
        ];

        $this->apiClientMock->expects($this->once())
            ->method('transactionStatus')
            ->with(self::TRANSFER_DATA['sessionId'])
            ->willReturn($response);

        $result = array_merge($response, [
            'data' => [
                ApiTransaction::SESSION_ID => self::TRANSFER_DATA['sessionId'],
                ApiTransaction::STATUS => ApiTransaction::STATUS_NO_PAYMENT,
                ApiTransaction::AMOUNT => null,
                'paymentMethod' => null,
            ],
        ]);

        $this->assertEquals($result, $this->model->placeRequest($this->transferMock));
    }

    public function testPlaceRequestException(): void
    {
        $this->expectException(ClientException::class);
        $this->apiClientMock->expects($this->once())
            ->method('transactionStatus')
            ->willThrowException(new \Exception('Error'));

        $this->loggerMock->expects($this->once())->method('critical');

        $this->model->placeRequest($this->transferMock);
    }
}
