<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\PaymentMethodsModifierInterface;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethods;
use PayPro\Przelewy24\Model\InstalmentFilter;
use PayPro\Przelewy24\Model\LanguageResolver;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PayPro\Przelewy24\Model\PaymentMethods;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PaymentMethodsTest extends TestCase
{
    /**
     * @var \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface[]
     */
    private $paymentMethods;

    /**
     * @var \PayPro\Przelewy24\Api\PaymentMethodsModifierInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodsModifierMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PayPro\Przelewy24\Model\InstalmentFilter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $instalmentFilterMock;

    /**
     * @var \PayPro\Przelewy24\Model\PaymentMethods
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMethods = [
            new ApiPaymentMethod(1, 'payment1'),
            new ApiPaymentMethod(2, 'payment2'),
        ];

        $apiConfigMock = $this->getMockBuilder(ApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfigMock->expects($this->once())->method('get')->willReturn(['config']);
        $languageResolverMock = $this->getMockBuilder(LanguageResolver::class)->disableOriginalConstructor()->getMock();
        $languageResolverMock->expects($this->once())->method('resolve')->willReturn('en');
        $this->paymentMethodsModifierMock = $this->getMockForAbstractClass(PaymentMethodsModifierInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $apiPaymentMethodsMock = $this->getMockBuilder(ApiPaymentMethods::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiPaymentMethodsMock->expects($this->once())
            ->method('execute')
            ->with('en', ['config'])
            ->willReturn($this->paymentMethods);

        $this->instalmentFilterMock = $this->getMockBuilder(InstalmentFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new PaymentMethods(
            $apiConfigMock,
            $apiPaymentMethodsMock,
            $languageResolverMock,
            $this->paymentMethodsModifierMock,
            $this->instalmentFilterMock,
            $this->loggerMock
        );
    }

    public function testExecute(): void
    {
        $this->paymentMethodsModifierMock->expects($this->once())->method('modify')->willReturn($this->paymentMethods);
        $this->loggerMock->expects($this->never())->method('error');
        $this->instalmentFilterMock->expects($this->never())->method('execute');
        $this->assertEquals($this->paymentMethods, $this->model->execute());
    }

    public function testExecuteWithModifierException(): void
    {
        $exception = new LocalizedException(__('error'));
        $this->instalmentFilterMock->expects($this->never())->method('execute');
        $this->paymentMethodsModifierMock->expects($this->once())
            ->method('modify')
            ->with($this->paymentMethods)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('error')->with(__('error'), ['exception' => $exception]);
        $this->assertEquals([], $this->model->execute());
    }

    public function testExecuteWithAmount(): void
    {
        $this->paymentMethodsModifierMock->expects($this->once())->method('modify')->willReturn($this->paymentMethods);
        $this->loggerMock->expects($this->never())->method('error');
        $this->instalmentFilterMock->expects($this->once())->method('execute');
        $this->assertEquals($this->paymentMethods, $this->model->execute(50.0));
    }
}
