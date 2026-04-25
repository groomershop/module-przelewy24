<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Ui;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use PayPro\Przelewy24\Gateway\Config\ApplePayConfig;
use PayPro\Przelewy24\Gateway\Config\BlikConfig;
use PayPro\Przelewy24\Gateway\Config\CardConfig;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Gateway\Config\GooglePayConfig;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PayPro\Przelewy24\Model\Ui\Logo;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testGetConfig(): void
    {
        $commonConfigMock = $this->getMockBuilder(CommonConfig::class)->disableOriginalConstructor()->getMock();
        $gatewayConfigMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();
        $cardConfigMock = $this->getMockBuilder(CardConfig::class)->disableOriginalConstructor()->getMock();
        $googlePayConfigMock = $this->getMockBuilder(GooglePayConfig::class)->disableOriginalConstructor()->getMock();
        $applePayConfigMock = $this->getMockBuilder(ApplePayConfig::class)->disableOriginalConstructor()->getMock();
        $blikConfigMock = $this->getMockBuilder(BlikConfig::class)->disableOriginalConstructor()->getMock();
        $urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $urlMock->expects($this->any())->method('getUrl')->willReturn('url');
        $logoMock = $this->getMockBuilder(Logo::class)->disableOriginalConstructor()->getMock();
        $localeResolverMock = $this->createMock(Resolver::class);
        $localeResolverMock->expects($this->any())->method('getLocale')->willReturn('pl_PL');
        $model = new ConfigProvider(
            $commonConfigMock,
            $gatewayConfigMock,
            $cardConfigMock,
            $blikConfigMock,
            $googlePayConfigMock,
            $applePayConfigMock,
            $urlMock,
            $logoMock,
            $localeResolverMock
        );

        $this->assertTrue(isset($model->getConfig()['payment'][ConfigProvider::CODE]));
        $this->assertTrue(isset($model->getConfig()['payment'][ConfigProvider::CARD_CODE]));
        $this->assertTrue(isset($model->getConfig()['payment'][ConfigProvider::BLIK_CODE]));
        $this->assertTrue(isset($model->getConfig()['payment'][ConfigProvider::GOOGLE_PAY_CODE]));
        $this->assertTrue(isset($model->getConfig()['payment'][ConfigProvider::APPLE_PAY_CODE]));
    }
}
