<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Status;

use PayPro\Przelewy24\Api\WebhookHandlerInterface;
use PayPro\Przelewy24\Controller\Status\Refund;

class RefundTest extends StatusControllerTestCase
{
    /**
     * @var \PayPro\Przelewy24\Controller\Status\Refund
     */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new Refund(
            $this->resultRawFactoryMock,
            $this->requestMock,
            $this->webhookHandlerMock,
            $this->loggerMock
        );
    }

    public function testExecute(): void
    {
        $this->requestMock->expects($this->once())->method('getContent')->willReturn('{"data": "refund_data"}');
        $this->webhookHandlerMock->expects($this->once())->method('handle')->with(['data' => 'refund_data']);
        $this->resultMock->expects($this->once())
            ->method('setContents')
            ->with(WebhookHandlerInterface::SUCCESS_RESPONSE)
            ->willReturnSelf();

        $this->assertNull($this->controller->createCsrfValidationException($this->requestMock));
        $this->assertTrue($this->controller->validateForCsrf($this->requestMock));
        $this->assertEquals($this->resultMock, $this->controller->execute());
    }

    public function testError(): void
    {
        $this->requestMock->expects($this->once())->method('getContent')->willReturn('{"data": "refund_data"}');
        $this->webhookHandlerMock->expects($this->once())
            ->method('handle')
            ->with(['data' => 'refund_data'])
            ->willThrowException(new \Exception('Refund webhook error'));

        $this->resultMock->expects($this->once())
            ->method('setContents')
            ->with(WebhookHandlerInterface::FAILURE_RESPONSE)
            ->willReturnSelf();

        $this->loggerMock->expects($this->once())->method('error');

        $this->assertEquals($this->resultMock, $this->controller->execute());
    }
}
