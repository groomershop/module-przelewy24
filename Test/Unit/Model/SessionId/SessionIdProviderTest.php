<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\SessionId;

use PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver;
use PayPro\Przelewy24\Model\SessionId\SessionIdPrzelewy24MethodIdResolver;
use PayPro\Przelewy24\Model\SessionId\SessionIdMagentoPaymentMethodResolver;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use PayPro\Przelewy24\Model\SessionId\SessionIdProvider;
use PHPUnit\Framework\TestCase;

class SessionIdProviderTest extends TestCase
{
    public function testGet(): void
    {
        $sessionIdVersionMetadataResolverMock = $this->getMockBuilder(SessionIdVersionMetadataResolver::class)
            ->disableOriginalConstructor()->getMock();
        $sessionIdPrzelewy24MethodIdResolverMock = $this->getMockBuilder(SessionIdPrzelewy24MethodIdResolver::class)
            ->disableOriginalConstructor()->getMock();
        $sessionIdMagentoPaymentMethodResolverMock = $this->getMockBuilder(SessionIdMagentoPaymentMethodResolver::class)
            ->disableOriginalConstructor()->getMock();
        $identityGeneratorMock = $this->getMockForAbstractClass(IdentityGeneratorInterface::class);

        $paymentMethod = 'przelewy24';
        $additionalData = ['key' => 'value'];

        $sessionIdVersionMetadataResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('PART1_');

        $sessionIdPrzelewy24MethodIdResolverMock->expects($this->once())
            ->method('resolve')
            ->with($paymentMethod, $additionalData)
            ->willReturn('PART2_');

        $sessionIdMagentoPaymentMethodResolverMock->expects($this->once())
            ->method('resolve')
            ->with($paymentMethod)
            ->willReturn('PART3_');

        $identityGeneratorMock->expects($this->once())
            ->method('generateId')
            ->willReturn('XYZ123');

        $provider = new SessionIdProvider(
            $sessionIdVersionMetadataResolverMock,
            $sessionIdPrzelewy24MethodIdResolverMock,
            $sessionIdMagentoPaymentMethodResolverMock,
            $identityGeneratorMock
        );

        $expected = 'PART1_PART2_PART3_XYZ123';
        $this->assertEquals($expected, $provider->get($paymentMethod, $additionalData));
    }
}
