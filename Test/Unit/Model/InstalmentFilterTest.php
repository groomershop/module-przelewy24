<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Model\InstalmentFilter;
use PHPUnit\Framework\TestCase;

class InstalmentFilterTest extends TestCase
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GatewayConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var \PayPro\Przelewy24\Model\InstalmentFilter
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();
        $this->model = new InstalmentFilter($this->configMock);
    }

    public function testExecute(): void
    {
        $method1 = $this->getMockForAbstractClass(ApiPaymentMethodInterface::class);
        $method2 = $this->getMockForAbstractClass(ApiPaymentMethodInterface::class);
        $method3 = $this->getMockForAbstractClass(ApiPaymentMethodInterface::class);
        $method1->expects($this->any())->method('getId')->willReturn(1);
        $method2->expects($this->any())->method('getId')->willReturn(2);
        $method3->expects($this->any())->method('getId')->willReturn(3);

        $this->configMock->expects($this->once())->method('getInstalmentMap')->willReturn([
            1 => ['from' => 10, 'to' => 50],
            2 => ['from' => 100, 'to' => 500],
        ]);

        $this->assertEqualsCanonicalizing([
            $method2,
            $method3,
        ], $this->model->execute([
            $method1,
            $method2,
            $method3,
        ], 100.0));
    }

    public function testExecuteEmptyMap(): void
    {
        $method = $this->getMockForAbstractClass(ApiPaymentMethodInterface::class);
        $this->configMock->expects($this->once())->method('getInstalmentMap')->willReturn([]);
        $this->assertEquals([$method], $this->model->execute([$method], 100.0));
    }
}
