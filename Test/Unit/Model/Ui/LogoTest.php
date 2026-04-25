<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Ui;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use PayPro\Przelewy24\Model\Ui\Logo;
use PHPUnit\Framework\TestCase;

class LogoTest extends TestCase
{
    public function testGetUrl(): void
    {
        $assetUrl = 'https://przelewy24.pl/logo.svg';
        $model = $this->getModel($assetUrl);
        $this->assertEquals($assetUrl, $model->getUrl());
    }

    public function testCardUrl(): void
    {
        $assetUrl = 'https://przelewy24.pl/logo-card.svg';
        $model = $this->getModel($assetUrl);
        $this->assertEquals($assetUrl, $model->getCardUrl());
    }

    public function testBlikUrl(): void
    {
        $assetUrl = 'https://przelewy24.pl/logo-blik.svg';
        $model = $this->getModel($assetUrl);
        $this->assertEquals($assetUrl, $model->getBlikUrl());
    }

    public function testGooglePayUrl(): void
    {
        $assetUrl = 'https://przelewy24.pl/logo-google-pay.svg';
        $model = $this->getModel($assetUrl);
        $this->assertEquals($assetUrl, $model->getGooglePayUrl());
    }

    public function testApplePayUrl(): void
    {
        $assetUrl = 'https://przelewy24.pl/logo-apple-pay.svg';
        $model = $this->getModel($assetUrl);
        $this->assertEquals($assetUrl, $model->getApplePayUrl());
    }

    public function testGetUrlException(): void
    {
        $assetRepositoryMock = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->once())->method('isSecure')->willReturn(true);
        $assetRepositoryMock->expects($this->once())
            ->method('createAsset')
            ->willThrowException(new LocalizedException(__('Error')));

        $model = new Logo($requestMock, $assetRepositoryMock);

        $this->assertNull($model->getUrl());
    }

    private function getModel(string $assetUrl): Logo
    {
        $assetRepositoryMock = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $assetMock = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $assetMock->expects($this->once())->method('getUrl')->willReturn($assetUrl);
        $requestMock->expects($this->once())->method('isSecure')->willReturn(true);
        $assetRepositoryMock->expects($this->once())
            ->method('createAsset')->willReturn($assetMock);

        return new Logo($requestMock, $assetRepositoryMock);
    }
}
