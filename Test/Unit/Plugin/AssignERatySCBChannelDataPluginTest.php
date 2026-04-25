<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Plugin;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Plugin\AssignERatySCBChannelDataPlugin;
use PHPUnit\Framework\TestCase;

class AssignERatySCBChannelDataPluginTest extends TestCase
{
    public function testAfterBuild(): void
    {
        $subjectMock = $this->getMockForAbstractClass(BuilderInterface::class);
        $model = new AssignERatySCBChannelDataPlugin();
        $this->assertEquals([
            'method' => ApiPaymentMethodInterface::ERATY_SCB_ID,
            'channel' => 2048,
        ], $model->afterBuild($subjectMock, ['method' => ApiPaymentMethodInterface::ERATY_SCB_ID]));
    }
}
