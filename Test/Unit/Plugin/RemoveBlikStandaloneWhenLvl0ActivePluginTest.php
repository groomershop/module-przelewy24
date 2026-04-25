<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Plugin;

use PayPro\Przelewy24\Gateway\Config\BlikConfig;
use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Plugin\RemoveBlikStandaloneWhenLvl0ActivePlugin;
use PHPUnit\Framework\TestCase;

class RemoveBlikStandaloneWhenLvl0ActivePluginTest extends TestCase
{
    private const STANDALONE_METHODS = [
        ['id' => 1, 'name' => 'method1'],
        ['id' => 2, 'name' => 'method2'],
        ['id' => 3, 'name' => 'method3'],
        ['id' => 4, 'name' => 'method4'],
        ['id' => 5, 'name' => 'method5'],
        ['id' => 6, 'name' => 'method6'],
    ];

    private const BLIK_IN_STORE_METHOD = ['id' => BlikConfig::BLIK_IN_STORE_ID, 'name' => 'blik in store'];

    public function testBlikLevel0Disabled(): void
    {
        $gatewayConfigMock = $this->createMock(GatewayConfig::class);
        $blikConfigMock = $this->createMock(BlikConfig::class);
        $blikConfigMock->expects($this->once())->method('isActive')->willReturn(false);

        $plugin = new RemoveBlikStandaloneWhenLvl0ActivePlugin($blikConfigMock);

        $standaloneMethods = array_merge(
            self::STANDALONE_METHODS,
            [
                self::BLIK_IN_STORE_METHOD,
            ]
        );

        $this->assertEquals(
            $standaloneMethods,
            $plugin->afterGetStandaloneMethods($gatewayConfigMock, $standaloneMethods)
        );
    }

    public function testBlikLevel0Enabled(): void
    {
        $gatewayConfigMock = $this->createMock(GatewayConfig::class);
        $blikConfigMock = $this->createMock(BlikConfig::class);
        $blikConfigMock->expects($this->once())->method('isActive')->willReturn(true);

        $plugin = new RemoveBlikStandaloneWhenLvl0ActivePlugin($blikConfigMock);

        $standaloneMethods = array_merge(
            self::STANDALONE_METHODS,
            [
                self::BLIK_IN_STORE_METHOD,
            ]
        );

        $this->assertEquals(
            self::STANDALONE_METHODS,
            $plugin->afterGetStandaloneMethods($gatewayConfigMock, $standaloneMethods)
        );
    }
}
