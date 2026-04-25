<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\SessionId;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PayPro\Przelewy24\Model\SessionId\PackageVersionProvider;
use PHPUnit\Framework\TestCase;

class PackageVersionProviderTest extends TestCase
{
    public function testBuild(): void
    {
        $componentRegistrarMock = $this->getMockForAbstractClass(ComponentRegistrarInterface::class);
        $readFactoryMock = $this->getMockBuilder(ReadFactory::class)->disableOriginalConstructor()->getMock();
        $readInterfaceMock = $this->getMockForAbstractClass(ReadInterface::class);
        $componentRegistrarMock->expects($this->once())->method('getPath')->with('module', 'PayPro_Przelewy24')
            ->willReturn('path');
        $readFactoryMock->expects($this->once())->method('create')->with('path')->willReturn($readInterfaceMock);
        $readInterfaceMock->expects($this->once())->method('readFile')->with('composer.json')
            ->willReturn('{"version": "1.0.0"}');

        $model = new PackageVersionProvider(
            $componentRegistrarMock,
            $readFactoryMock
        );

        $this->assertEquals($model->get(), '1.0.0');
    }
}
