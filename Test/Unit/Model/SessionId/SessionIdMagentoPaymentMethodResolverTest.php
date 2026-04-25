<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\SessionId;

use Magento\Authorization\Model\UserContextInterface;
use PayPro\Przelewy24\Gateway\Config\CardConfig;
use PayPro\Przelewy24\Model\SessionId\SessionIdMagentoPaymentMethodResolver;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;

class SessionIdMagentoPaymentMethodResolverTest extends TestCase
{
    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        string $paymentMethod,
        int $userId,
        bool $c2pEnabled,
        bool $c2pEnabledForGuests,
        string $expected
    ): void {
        $userContextMock = $this->createMock(UserContextInterface::class);
        $cardConfigMock = $this->createMock(CardConfig::class);

        $userContextMock->method('getUserId')->willReturn($userId);
        $cardConfigMock->method('isC2pEnabled')->willReturn($c2pEnabled);
        $cardConfigMock->method('isC2pEnabledForGuests')->willReturn($c2pEnabledForGuests);

        $resolver = new SessionIdMagentoPaymentMethodResolver($userContextMock, $cardConfigMock);

        $this->assertSame($expected, $resolver->resolve($paymentMethod));
    }

    public static function resolveDataProvider(): array
    {
        return [
            'blik code' => [
                'paymentMethod' => ConfigProvider::BLIK_CODE,
                'userId' => 0,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => 'b0_',
            ],
            'blik vault code' => [
                'paymentMethod' => ConfigProvider::BLIK_VAULT_CODE,
                'userId' => 1,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => 'b0oc_',
            ],
            'card vault code' => [
                'paymentMethod' => ConfigProvider::CARD_VAULT_CODE,
                'userId' => 5,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => 'ccoc_',
            ],
            'google pay code' => [
                'paymentMethod' => ConfigProvider::GOOGLE_PAY_CODE,
                'userId' => 2,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => 'gp_',
            ],
            'apple pay code' => [
                'paymentMethod' => ConfigProvider::APPLE_PAY_CODE,
                'userId' => 3,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => 'ap_',
            ],
            'card user logged in and c2p enabled' => [
                'paymentMethod' => ConfigProvider::CARD_CODE,
                'userId' => 10,
                'c2pEnabled' => true,
                'c2pEnabledForGuests' => false,
                'expected' => 'ccc2p_',
            ],
            'card guest and c2p enabled for guests' => [
                'paymentMethod' => ConfigProvider::CARD_CODE,
                'userId' => 0,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => true,
                'expected' => 'ccc2p_',
            ],
            'card with no c2p' => [
                'paymentMethod' => ConfigProvider::CARD_CODE,
                'userId' => 0,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => 'cc_',
            ],
            'unknown method' => [
                'paymentMethod' => 'unknown_method',
                'userId' => 0,
                'c2pEnabled' => false,
                'c2pEnabledForGuests' => false,
                'expected' => '',
            ],
        ];
    }
}
