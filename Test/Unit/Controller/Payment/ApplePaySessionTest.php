<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Payment;

use Magento\Framework\App\RequestContentInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use PayPro\Przelewy24\Controller\Payment\ApplePaySession;
use PayPro\Przelewy24\Model\ApplePay\RequestApplePayPaymentSession;
use PHPUnit\Framework\TestCase;

class ApplePaySessionTest extends TestCase
{
    private const VALIDATION_URL = 'https://apple-pay-gateway.apple.com/paymentservices/paymentSession';

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestContentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \PayPro\Przelewy24\Model\ApplePay\RequestApplePayPaymentSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestApplePaySessionMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultMock;

    /**
     * @var \PayPro\Przelewy24\Controller\Payment\ApplePaySession
     */
    private $controller;

    protected function setUp(): void
    {
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->requestMock = $this->createMock(RequestContentInterface::class);
        $this->requestApplePaySessionMock = $this->createMock(RequestApplePayPaymentSession::class);
        $this->resultMock = $this->createMock(Json::class);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);

        $this->requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn('{"validationUrl": "' . self::VALIDATION_URL . '"}');

        $this->controller = new ApplePaySession(
            $this->resultFactoryMock,
            $this->requestMock,
            $this->requestApplePaySessionMock
        );
    }

    public function testExecute(): void
    {
        $this->requestApplePaySessionMock->expects($this->once())
            ->method('execute')
            ->with(self::VALIDATION_URL)
            ->willReturn(['some data']);

        $this->resultMock->expects($this->once())->method('setData')->with(['some data'])->willReturnSelf();

        $this->assertEquals($this->resultMock, $this->controller->execute());
    }

    public function testExecuteWithEmptySessionObject(): void
    {
        $this->requestApplePaySessionMock->expects($this->once())
            ->method('execute')
            ->with(self::VALIDATION_URL)
            ->willReturn([]);

        $this->resultMock->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->resultMock->expects($this->once())->method('setData')->with([])->willReturnSelf();

        $this->assertEquals($this->resultMock, $this->controller->execute());
    }
}
