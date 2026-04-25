<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PayPro\Przelewy24\Model\Api\ApiClient;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    private $mockHandler;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiClient
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        $clientFactoryMock = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($client);

        $this->model = new ApiClient($clientFactoryMock, 'url', 'username', 'password');
    }

    public function testTestAccess(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->testAccess());
    }

    public function testPaymentMethods(): void
    {
        $response = [
            'data' => [
                'data1' => 1,
                'data2' => 2,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response['data'], $this->model->paymentMethods());
    }

    public function testRefundTransaction(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->refundTransaction([]));
    }

    public function testRefundInfo(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->refundInfo(1234));
    }

    public function testVerifyTransaction(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->verifyTransaction([]));
    }

    public function testRegisterTransaction(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->registerTransaction([]));
    }

    public function testTransactionStatus(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->transactionStatus('uuid'));
    }

    public function testCardInfo(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->cardInfo(3000001));
    }

    public function testChargeCard(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->chargeCard('token'));
    }

    public function testBlikChargeByCode(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->blikChargeByCode([]));
    }

    public function testBlikChargeByAlias(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($response))
        );

        $this->assertEquals($response, $this->model->blikChargeByAlias([]));
    }

    public function testNonArrayResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(true))
        );

        $this->assertEquals([], $this->model->registerTransaction([]));
    }

    public function testRequestException(): void
    {
        $response = [
            'data1' => 1,
            'data2' => 2,
        ];

        $this->mockHandler->append(new ClientException(
            'Something went wrong',
            new Request('GET', 'test'),
            new Response(400, ['Content-Type' => 'application/json'], (string) json_encode($response))
        ));

        $this->assertEquals($response, $this->model->registerTransaction([]));
    }
}
