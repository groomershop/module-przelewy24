<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Gateway\Config\GooglePayConfig;
use PHPUnit\Framework\TestCase;

class GooglePayConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GooglePayConfig
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = new GooglePayConfig($this->scopeConfigMock);
    }

    public function testIsActive(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/active',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isActive());
    }

    public function testIsCurrencyAllowed(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/allowed_currencies',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('PLN');

        $this->assertTrue($this->model->isCurrencyAllowed('PLN'));
    }

    public function testGetMerchantId(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/merchant_id',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('BCR00000000000SD');

        $this->assertEquals('BCR00000000000SD', $this->model->getMerchantId());
    }

    public function testGetAuthMethods(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/authentication_methods',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('PAN_ONLY,CRYPTOGRAM_3DS');

        $this->assertEquals(['PAN_ONLY', 'CRYPTOGRAM_3DS'], $this->model->getAuthMethods());
    }

    public function testGetCardNetworks(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_google_pay/card_networks',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('MASTERCARD,VISA');

        $this->assertEquals(['MASTERCARD', 'VISA'], $this->model->getCardNetworks());
    }
}
