<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Webhook;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Model\Webhook\WebhookPayload;
use PHPUnit\Framework\TestCase;

class WebhookPayloadTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)->disableOriginalConstructor()->getMock();
    }

    public function testGet(): void
    {
        $this->requestMock->expects($this->once())->method('getContent')->willReturn('{"data": 1}');
        $model = new WebhookPayload($this->requestMock);
        $this->assertEquals(['data' => 1], $model->get());
    }

    public function testGetLog(): void
    {
        $this->requestMock->expects($this->once())->method('getContent')->willReturn('{"data": 1, "sign": 2}');
        $model = new WebhookPayload($this->requestMock);
        $this->assertEquals(['data' => 1, 'sign' => 2], $model->get());
        $this->assertEquals(['data' => 1], $model->getLog());
    }

    public function testInvalidRequestInstance(): void
    {
        $this->expectException(LocalizedException::class);
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        new WebhookPayload($requestMock);
    }

    public function testInvalidPayload(): void
    {
        $this->expectException(LocalizedException::class);
        $this->requestMock->expects($this->once())->method('getContent')->willReturn('true');
        (new WebhookPayload($this->requestMock))->get();
    }

    public function testInvalidPayloadLogging(): void
    {
        $this->requestMock->expects($this->once())->method('getContent')->willReturn('true');
        $this->assertEquals([], (new WebhookPayload($this->requestMock))->getLog());
    }
}
