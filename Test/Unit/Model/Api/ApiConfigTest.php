<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PHPUnit\Framework\TestCase;

class ApiConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = new ApiConfig($this->scopeConfigMock);
    }

    public function testGetProductionConfig(): void
    {
        $this->scopeConfigMock->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                ['payment/przelewy24/pos_id', 'store', null],
                ['payment/przelewy24/report_key', 'store', null],
                ['payment/przelewy24/production_url', 'store', null]
            )
            ->willReturnOnConsecutiveCalls('username', 'password', 'https://production.przelewy24.pl');

        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with('payment/przelewy24/test_mode')
            ->willReturn(false);

        $this->assertEquals([
            ApiConfig::URL => 'https://production.przelewy24.pl',
            ApiConfig::USERNAME => 'username',
            ApiConfig::PASSWORD => 'password',
        ], $this->model->get());
    }

    public function testGetSandboxConfig(): void
    {
        $this->scopeConfigMock->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                ['payment/przelewy24/pos_id', 'store', null],
                ['payment/przelewy24/report_key', 'store', null],
                ['payment/przelewy24/sandbox_url', 'store', null]
            )
            ->willReturnOnConsecutiveCalls('username', 'password', 'https://sandbox.przelewy24.pl');

        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with('payment/przelewy24/test_mode')
            ->willReturn(true);

        $this->assertEquals([
            ApiConfig::URL => 'https://sandbox.przelewy24.pl',
            ApiConfig::USERNAME => 'username',
            ApiConfig::PASSWORD => 'password',
        ], $this->model->get());
    }
}
