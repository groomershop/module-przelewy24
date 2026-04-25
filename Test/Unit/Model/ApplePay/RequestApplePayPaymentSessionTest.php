<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\ApplePay;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Payment\Model\Method\Logger;
use PayPro\Przelewy24\Gateway\Config\ApplePayConfig;
use PayPro\Przelewy24\Model\ApplePay\RequestApplePayPaymentSession;
use PHPUnit\Framework\TestCase;

class RequestApplePayPaymentSessionTest extends TestCase
{
    /**
     * @var \GuzzleHttp\ClientFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientFactoryMock;

    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentLoggerMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\ApplePayConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $applePayConfigMock;

    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    private $mockHandler;

    /**
     * @var \PayPro\Przelewy24\Model\ApplePay\RequestApplePayPaymentSession
     */
    private $model;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(ClientFactory::class);
        $this->paymentLoggerMock = $this->createMock(Logger::class);
        $this->applePayConfigMock = $this->createMock(ApplePayConfig::class);

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        $this->clientFactoryMock->expects($this->atMost(1))->method('create')->willReturn($client);

        $this->model = new RequestApplePayPaymentSession(
            $this->clientFactoryMock,
            $this->paymentLoggerMock,
            $this->applePayConfigMock
        );
    }

    public function testExecute(): void
    {
        $this->applePayConfigMock->expects($this->once())->method('getCertificateFilePath')->willReturn('/some/path');
        $this->applePayConfigMock->expects($this->once())->method('getSSLKeyFilePath')->willReturn('/some/path2');
        $this->paymentLoggerMock->expects($this->never())->method('debug');
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['some data']))
        );

        $this->assertEquals(['some data'], $this->model->execute('https://url.com/'));
    }

    public function testExecuteWithoutCertificate(): void
    {
        $this->applePayConfigMock->expects($this->once())->method('getCertificateFilePath')->willReturn(null);
        $this->applePayConfigMock->expects($this->once())->method('getSSLKeyFilePath')->willReturn('/some/path');
        $this->paymentLoggerMock->expects($this->once())->method('debug');

        $this->assertEquals([], $this->model->execute('https://url.com/'));
    }

    public function testExecuteWithErrorResponse(): void
    {
        $this->applePayConfigMock->expects($this->once())->method('getCertificateFilePath')->willReturn('/some/path');
        $this->applePayConfigMock->expects($this->once())->method('getSSLKeyFilePath')->willReturn('/some/path2');
        $this->paymentLoggerMock->expects($this->once())->method('debug');
        $this->mockHandler->append(
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        );

        $this->assertEquals([], $this->model->execute('https://url.com/'));
    }
}
