<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\SessionId;

use PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use PayPro\Przelewy24\Model\SessionId\RefundsUuIdProvider;
use PHPUnit\Framework\TestCase;

class RefundsUuIdProviderTest extends TestCase
{
    public function testGet(): void
    {
        $sessionIdVersionMetadataResolverMock = $this->getMockBuilder(SessionIdVersionMetadataResolver::class)
            ->disableOriginalConstructor()->getMock();
        $identityGeneratorMock = $this->getMockForAbstractClass(IdentityGeneratorInterface::class);

        $sessionIdVersionMetadataResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('PREFIX-');

        $identityGeneratorMock->expects($this->once())
            ->method('generateId')
            ->willReturn('123456');

        $model = new RefundsUuIdProvider(
            $sessionIdVersionMetadataResolverMock,
            $identityGeneratorMock
        );

        $this->assertEquals('PREFIX-123456', $model->get());
    }
}
