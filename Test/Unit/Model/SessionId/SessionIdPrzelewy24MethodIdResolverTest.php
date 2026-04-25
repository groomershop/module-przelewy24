<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\SessionId;

use PayPro\Przelewy24\Gateway\Config\ApplePayConfig;
use PayPro\Przelewy24\Gateway\Config\GooglePayConfig;
use PayPro\Przelewy24\Gateway\Config\CardConfig;
use PayPro\Przelewy24\Model\SessionId\SessionIdPrzelewy24MethodIdResolver;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;

class SessionIdPrzelewy24MethodIdResolverTest extends TestCase
{
    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        string $paymentMethod,
        ?array $paymentAdditional,
        array $configMethodIds,
        string $expected
    ): void {
        $applePayConfigMock = $this->createMock(ApplePayConfig::class);
        $googlePayConfigMock = $this->createMock(GooglePayConfig::class);
        $cardConfigMock = $this->createMock(CardConfig::class);

        $cardConfigMock->method('getMethodId')->willReturn($configMethodIds['card']);
        $googlePayConfigMock->method('getMethodId')->willReturn($configMethodIds['googlePay']);
        $applePayConfigMock->method('getMethodId')->willReturn($configMethodIds['applePay']);

        $resolver = new SessionIdPrzelewy24MethodIdResolver(
            $applePayConfigMock,
            $googlePayConfigMock,
            $cardConfigMock
        );

        $this->assertSame($expected, $resolver->resolve($paymentMethod, $paymentAdditional));
    }

    public static function resolveDataProvider(): array
    {
        return [
            'custom method in additional' => [
                'paymentMethod' => 'any',
                'paymentAdditional' => ['method' => 'custom123'],
                'configMethodIds' => ['card' => 241, 'googlePay' => 265, 'applePay' => 252],
                'expected' => 'dirmet{custom123}_',
            ],
            'card method (vault and non-vault use same config)' => [
                'paymentMethod' => ConfigProvider::CARD_CODE,
                'paymentAdditional' => [],
                'configMethodIds' => ['card' => 241, 'googlePay' => 265, 'applePay' => 252],
                'expected' => 'dirmet{241}_',
            ],
            'blik method' => [
                'paymentMethod' => ConfigProvider::BLIK_CODE,
                'paymentAdditional' => [],
                'configMethodIds' => ['card' => 241, 'googlePay' => 265, 'applePay' => 252],
                'expected' => 'dirmet{' . \PayPro\Przelewy24\Gateway\Config\BlikConfig::BLIK_IN_STORE_ID . '}_',
            ],
            'google pay method' => [
                'paymentMethod' => ConfigProvider::GOOGLE_PAY_CODE,
                'paymentAdditional' => [],
                'configMethodIds' => ['card' => 241, 'googlePay' => 999, 'applePay' => 888],
                'expected' => 'dirmet{999}_',
            ],
            'apple pay method' => [
                'paymentMethod' => ConfigProvider::APPLE_PAY_CODE,
                'paymentAdditional' => [],
                'configMethodIds' => ['card' => 241, 'googlePay' => 265, 'applePay' => 777],
                'expected' => 'dirmet{777}_',
            ],
            'base p24 code (pg)' => [
                'paymentMethod' => ConfigProvider::CODE,
                'paymentAdditional' => [],
                'configMethodIds' => ['card' => 241, 'googlePay' => 265, 'applePay' => 252],
                'expected' => 'pg_',
            ],
            'unknown method returns empty' => [
                'paymentMethod' => 'some_unknown_method',
                'paymentAdditional' => [],
                'configMethodIds' => ['card' => 241, 'googlePay' => 265, 'applePay' => 252],
                'expected' => '',
            ],
        ];
    }
}
