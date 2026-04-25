<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\SessionId;

use Magento\Framework\App\ProductMetadataInterface;
use PayPro\Przelewy24\Api\SessionId\PackageVersionProviderInterface;
use PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver;
use PHPUnit\Framework\TestCase;

class SessionIdVersionMetadataResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $productMetadataMock = $this->getMockForAbstractClass(ProductMetadataInterface::class);
        $packageVersionProviderMock = $this->getMockForAbstractClass(PackageVersionProviderInterface::class);

        $productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('2.4.6');

        $packageVersionProviderMock->expects($this->once())
            ->method('get')
            ->willReturn('1.2.3');

        $resolver = new SessionIdVersionMetadataResolver(
            $productMetadataMock,
            $packageVersionProviderMock
        );

        $expected = 'magp24{2.4.6:1.2.3}_';
        $this->assertEquals($expected, $resolver->resolve());
    }
}
