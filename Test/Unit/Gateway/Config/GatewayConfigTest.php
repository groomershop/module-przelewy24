<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PHPUnit\Framework\TestCase;

class GatewayConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GatewayConfig
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->model = new GatewayConfig($this->scopeConfigMock, $this->serializerMock);
    }

    public function testIsActive(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/active',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isActive());
    }

    public function testIsCurrencyAllowed(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/allowed_currencies',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('PLN,EUR,USD');

        $this->assertTrue($this->model->isCurrencyAllowed('PLN'));
    }

    public function testGetPaymentMethodsSortOrder(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/payment_methods',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('[data]');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('[data]')
            ->willReturn([
                ['id' => '1'],
                ['id' => '2'],
                ['id' => '3'],
            ]);

        $this->assertEquals(['1', '2', '3'], $this->model->getPaymentMethodsSortOrder());
    }

    public function testGetPaymentMethodsSortOrderInvalidData(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/payment_methods',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('invalid_data');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('invalid_data')
            ->willThrowException(new \InvalidArgumentException('error'));

        $this->assertNull($this->model->getPaymentMethodsSortOrder());
    }

    public function testIsSelectPaymentMethodInStoreEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/select_payment_method_in_store',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isSelectPaymentMethodInStoreEnabled());
    }

    public function testIsWaitForTransactionResultEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/wait_for_transaction_result',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isWaitForTransactionResultEnabled());
    }

    public function testGetInstalmentMap(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/instalment_map',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('some data');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('some data')
            ->willReturn([
                1 => ['from' => 1, 'to' => 2],
                2 => ['from' => 10, 'to' => 20],
                3 => ['from' => 100, 'to' => 200],
            ]);

        $this->assertEquals([
            1 => ['from' => 1, 'to' => 2],
            2 => ['from' => 10, 'to' => 20],
            3 => ['from' => 100, 'to' => 200],
        ], $this->model->getInstalmentMap());
    }

    public function testIsERatySCBPromoted(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/promote_eraty_scb',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isERatySCBPromoted());
    }

    public function testGetStandaloneMethods(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24/payment_methods',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('some data');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('some data')
            ->willReturn([
                ['id' => 1, 'name' => 'Method1', 'img' => 'img_url1', 'standalone' => true],
                ['id' => 2, 'name' => 'Method2', 'img' => 'img_url2', 'standalone' => false],
                ['id' => 3, 'name' => 'Method3', 'img' => 'img_url3', 'standalone' => true],
                ['id' => 4, 'name' => 'Method4', 'img' => 'img_url4'],
            ]);

        $this->assertEquals([
            1 => ['id' => 1, 'name' => 'Method1', 'img' => 'img_url1'],
            3 => ['id' => 3, 'name' => 'Method3', 'img' => 'img_url3'],
        ], $this->model->getStandaloneMethods());
    }
}
