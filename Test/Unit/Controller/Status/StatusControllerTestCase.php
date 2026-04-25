<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Status;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use PayPro\Przelewy24\Api\WebhookHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StatusControllerTestCase extends TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PayPro\Przelewy24\Api\WebhookHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $webhookHandlerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultRawFactoryMock = $this->getMockBuilder(RawFactory::class)->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)->disableOriginalConstructor()->getMock();
        $this->webhookHandlerMock = $this->getMockForAbstractClass(WebhookHandlerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->resultMock = $this->getMockBuilder(Raw::class)->disableOriginalConstructor()->getMock();

        $this->resultRawFactoryMock->expects($this->once())->method('create')->willReturn($this->resultMock);
    }
}
