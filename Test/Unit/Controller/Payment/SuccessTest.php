<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Payment;

use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PayPro\Przelewy24\Controller\Payment\Success;
use PHPUnit\Framework\TestCase;

class SuccessTest extends TestCase
{
    public function testExecute(): void
    {
        $pageTitleMock = $this->createMock(Title::class);
        $pageTitleMock->expects($this->once())->method('set');
        $pageConfigMock = $this->createMock(Config::class);
        $pageConfigMock->expects($this->once())->method('getTitle')->willReturn($pageTitleMock);
        $pageMock = $this->createMock(Page::class);
        $pageMock->expects($this->once())->method('getConfig')->willReturn($pageConfigMock);
        $pageFactoryMock = $this->createMock(PageFactory::class);
        $pageFactoryMock->expects($this->once())->method('create')->willReturn($pageMock);

        $controller = new Success($pageFactoryMock);

        $this->assertEquals($pageMock, $controller->execute());
    }
}
