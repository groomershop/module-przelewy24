<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PHPUnit\Framework\TestCase;

class CommonConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = new CommonConfig($this->scopeConfigMock);
    }

    public function testGetPosId(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/pos_id',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(111111);

        $this->assertEquals(111111, $this->model->getPosId());
    }

    public function testGetMerchantId(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/merchant_id',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(111111);

        $this->assertEquals(111111, $this->model->getMerchantId());
    }

    public function testGetMerchantName(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('name');

        $this->assertEquals('name', $this->model->getMerchantName());
    }

    public function testGetCrcKey(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/crc_key',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('crc_key');

        $this->assertEquals('crc_key', $this->model->getCrcKey());
    }

    public function testIsTestMode(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isTestMode());
    }

    public function testGetSandboxGatewayUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/sandbox_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('sandbox_url');

        $this->assertEquals('sandbox_url', $this->model->getGatewayUrl());
    }

    public function testGetProductionGatewayUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(false);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/production_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('production_url');

        $this->assertEquals('production_url', $this->model->getGatewayUrl());
    }

    public function testGetSandboxCardScriptUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_card/sandbox_script_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('sandbox_url');

        $this->assertEquals('sandbox_url', $this->model->getCardScriptUrl());
    }

    public function testGetProductionCardScriptUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(false);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_card/production_script_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('production_url');

        $this->assertEquals('production_url', $this->model->getCardScriptUrl());
    }

    public function testGetSandboxGooglePayScriptUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/sandbox_script_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('sandbox_url');

        $this->assertEquals('sandbox_url', $this->model->getGooglePayScriptUrl());
    }

    public function testGetProductionGooglePayScriptUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(false);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/production_script_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('production_url');

        $this->assertEquals('production_url', $this->model->getGooglePayScriptUrl());
    }

    public function testGetSandboxApplePayScriptUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/sandbox_script_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('sandbox_url');

        $this->assertEquals('sandbox_url', $this->model->getApplePayScriptUrl());
    }

    public function testGetProductionApplePayScriptUrl(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/test_mode',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(false);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/production_script_url',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('production_url');

        $this->assertEquals('production_url', $this->model->getApplePayScriptUrl());
    }

    public function testIsPaymentAutoUpdateEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'payment/przelewy24/payment_auto_update',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isPaymentAutoUpdateEnabled());
    }

    public function testGetPaymentTimeLimit(): void
    {
        $this->scopeConfigMock->expects($this->exactly(2))->method('getValue')->withConsecutive([
            'payment/przelewy24/payment_extra_time_limit_methods',
            ScopeInterface::SCOPE_STORE,
            null,
        ], [
            'payment/przelewy24/payment_time_limit',
            ScopeInterface::SCOPE_STORE,
            null,
        ])->willReturnOnConsecutiveCalls('1,2,3', '30');

        $this->assertEquals(30, $this->model->getPaymentTimeLimit(10));
    }

    public function testGetPaymentTimeLimitUnknownMethod(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/payment_time_limit',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('30');

        $this->assertEquals(30, $this->model->getPaymentTimeLimit(null));
    }

    public function testGetExtraPaymentTimeLimit(): void
    {
        $this->scopeConfigMock->expects($this->exactly(2))->method('getValue')->withConsecutive([
            'payment/przelewy24/payment_extra_time_limit_methods',
            ScopeInterface::SCOPE_STORE,
            null,
        ], [
            'payment/przelewy24/payment_extra_time_limit',
            ScopeInterface::SCOPE_STORE,
            null,
        ])->willReturnOnConsecutiveCalls('1,2,3', '720');

        $this->assertEquals(720, $this->model->getPaymentTimeLimit(1));
    }
}
