<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Webhook;

use Magento\Payment\Model\Method\Logger;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PHPUnit\Framework\TestCase;

class WebhookTestCase extends TestCase
{
    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentLoggerMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentLoggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $this->paymentLoggerMock->expects($this->once())->method('debug');

        $this->configMock = $this->getMockBuilder(CommonConfig::class)->disableOriginalConstructor()->getMock();
        $this->configMock->expects($this->once())->method('getCrcKey')->willReturn('crc');
    }
}
