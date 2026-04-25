<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Locale\ResolverInterface;
use PayPro\Przelewy24\Model\LanguageResolver;
use PHPUnit\Framework\TestCase;

class LanguageResolverTest extends TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeResolverMock;

    /**
     * @var \PayPro\Przelewy24\Model\LanguageResolver
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->model = new LanguageResolver($this->localeResolverMock);
    }

    public function testNoStoreIdResolve(): void
    {
        $this->localeResolverMock->expects($this->once())->method('getLocale')->willReturn('pl_PL');
        $this->localeResolverMock->expects($this->never())->method('emulate');
        $this->assertEquals('pl', $this->model->resolve());
    }

    public function testStoreIdResolve(): void
    {
        $this->localeResolverMock->expects($this->once())->method('emulate')->with(1)->willReturn('fr_FR');
        $this->localeResolverMock->expects($this->never())->method('getLocale');
        $this->assertEquals('fr', $this->model->resolve(1));
    }

    public function testInvalidLocale(): void
    {
        $this->localeResolverMock->expects($this->once())->method('getLocale')->willReturn('invalid');
        $this->localeResolverMock->expects($this->never())->method('emulate');
        $this->assertEquals(LanguageResolver::DEFAULT_LANGUAGE, $this->model->resolve());
    }

    public function testNoLocale(): void
    {
        $this->localeResolverMock->expects($this->never())->method('getLocale');
        $this->localeResolverMock->expects($this->once())->method('emulate')->willReturn(null);
        $this->assertEquals(LanguageResolver::DEFAULT_LANGUAGE, $this->model->resolve(1));
    }
}
