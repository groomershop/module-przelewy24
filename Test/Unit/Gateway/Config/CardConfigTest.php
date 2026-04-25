<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Gateway\Config\CardConfig;
use PHPUnit\Framework\TestCase;

class CardConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CardConfig
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = new CardConfig($this->scopeConfigMock);
    }

    public function testIsActive(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_card/active',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isActive());
    }

    public function testIsCurrencyAllowed(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_card/allowed_currencies',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('PLN,EUR,USD');

        $this->assertTrue($this->model->isCurrencyAllowed('PLN'));
    }
}
