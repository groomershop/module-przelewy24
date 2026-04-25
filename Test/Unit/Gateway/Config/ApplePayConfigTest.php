<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PayPro\Przelewy24\Gateway\Config\ApplePayConfig;
use PHPUnit\Framework\TestCase;

class ApplePayConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileSystemMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\ApplePayConfig
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->fileSystemMock = $this->createMock(Filesystem::class);

        $this->model = new ApplePayConfig($this->scopeConfigMock, $this->fileSystemMock);
    }

    public function testIsActive(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/active',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);

        $this->assertTrue($this->model->isActive());
    }

    public function testIsCurrencyAllowed(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/allowed_currencies',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('PLN');

        $this->assertTrue($this->model->isCurrencyAllowed('PLN'));
    }

    public function testGetDisplayName(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            Information::XML_PATH_STORE_INFO_NAME,
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('Luma Store');

        $this->assertEquals('Luma Store', $this->model->getDisplayName());
    }

    public function testGetMerchantIdentifier(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/merchant_identifier',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('merchant.com.luma.id');

        $this->assertEquals('merchant.com.luma.id', $this->model->getMerchantIdentifier());
    }

    public function testGetInitiativeContext(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            Store::XML_PATH_SECURE_BASE_URL,
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('https://luma.com/');

        $this->assertEquals('luma.com', $this->model->getInitiativeContext());
    }

    public function testGetSSLKeyFilePath(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/ssl_key',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('path/to/file');

        $directoryMock = $this->createMock(ReadInterface::class);
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryMock);
        $directoryMock->expects($this->once())->method('getAbsolutePath')->willReturn('/absolute/path/to/file');

        $this->assertEquals('/absolute/path/to/file', $this->model->getSSLKeyFilePath());
    }

    public function testGetSSLKeyFilePathWhenNull(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/ssl_key',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(null);

        $this->assertNull($this->model->getSSLKeyFilePath());
    }

    public function testGetCertificateFilePath(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/certificate',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn('path/to/file');

        $directoryMock = $this->createMock(ReadInterface::class);
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryMock);
        $directoryMock->expects($this->once())->method('getAbsolutePath')->willReturn('/absolute/path/to/file');

        $this->assertEquals('/absolute/path/to/file', $this->model->getCertificateFilePath());
    }

    public function testGetCertificateFilePathWhenNull(): void
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'payment/przelewy24_apple_pay/certificate',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(null);

        $this->assertNull($this->model->getCertificateFilePath());
    }
}
