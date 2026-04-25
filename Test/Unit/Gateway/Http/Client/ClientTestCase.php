<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\ApiClientInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientTestCase extends TestCase
{
    protected const TRANSFER_DATA = ['sessionId' => 'uuid', 'orderId' => 'id'];
    protected const URL = 'https://sandbox.przelewy24.pl';
    protected const USERNAME = 'username';
    protected const PASSWORD = 'password';

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $apiClientMock;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $apiClientFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentLoggerMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $transferMock;

    /**
     * @var \Magento\Payment\Gateway\Http\ClientInterface
     */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = $this->getMockForAbstractClass(ApiClientInterface::class);
        $this->apiClientFactoryMock = $this->getMockBuilder(ApiClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiClientFactoryMock->expects($this->once())->method('create')->with([
            'url' => self::URL,
            'username' => self::USERNAME,
            'password' => self::PASSWORD,
        ])->willReturn($this->apiClientMock);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->paymentLoggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $this->paymentLoggerMock->expects($this->once())->method('debug');

        $this->transferMock = $this->getMockForAbstractClass(TransferInterface::class);
        $this->transferMock->expects($this->exactly(2))->method('getBody')->willReturn(self::TRANSFER_DATA);
        $this->transferMock->expects($this->exactly(2))->method('getUri')->willReturn(self::URL);
        $this->transferMock->expects($this->exactly(2))->method('getAuthUsername')->willReturn(self::USERNAME);
        $this->transferMock->expects($this->once())->method('getAuthPassword')->willReturn(self::PASSWORD);
    }
}
