<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Controller\Adminhtml\Payment\Methods;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PayPro\Przelewy24\Model\LanguageResolver;
use PayPro\Przelewy24\Model\Api\ApiPaymentMethods;
use PHPUnit\Framework\TestCase;

class MethodsTest extends TestCase
{
    private const API_CONFIG = [
        ApiConfig::URL => 'https://sandbox.przelewy24.pl',
        ApiConfig::USERNAME => 'username',
        ApiConfig::PASSWORD => 'password',
    ];

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultMock;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiPaymentMethods|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiPaymentMethodsMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var \PayPro\Przelewy24\Controller\Adminhtml\Payment\Methods
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $apiConfigMock = $this->getMockBuilder(ApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfigMock->expects($this->once())->method('get')->willReturn(self::API_CONFIG);
        $resultFactoryMock = $this->getMockBuilder(ResultFactory::class)->disableOriginalConstructor()->getMock();
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultMock = $this->getMockBuilder(Json::class)->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->once())->method('getResultFactory')->willReturn($resultFactoryMock);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive([Methods::SCOPE_TYPE_PARAM, 'default'], [Methods::SCOPE_ID_PARAM])
            ->willReturnOnConsecutiveCalls('store', '1');

        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);

        $this->apiPaymentMethodsMock = $this->getMockBuilder(ApiPaymentMethods::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Methods(
            $contextMock,
            $apiConfigMock,
            $this->apiPaymentMethodsMock,
            $this->dataObjectProcessorMock
        );
    }

    public function testExecute(): void
    {
        $methods = [new ApiPaymentMethod(1, 'method_1', true), new ApiPaymentMethod(2, 'method_2', true)];
        $processedMethods = [['processed_method_1'], ['processed_method_2']];

        $this->apiPaymentMethodsMock->expects($this->once())
            ->method('execute')
            ->with(LanguageResolver::DEFAULT_LANGUAGE, self::API_CONFIG)
            ->willReturn($methods);

        $this->dataObjectProcessorMock->expects($this->exactly(count($methods)))
            ->method('buildOutputDataArray')
            ->withConsecutive(
                [$methods[0], ApiPaymentMethodInterface::class],
                [$methods[1], ApiPaymentMethodInterface::class]
            )->willReturnOnConsecutiveCalls($processedMethods[0], $processedMethods[1]);

        $this->resultMock->expects($this->once())->method('setData')->with($processedMethods);

        $this->assertSame($this->resultMock, $this->model->execute());
    }

    public function testExecuteException(): void
    {
        $this->apiPaymentMethodsMock->expects($this->once())
            ->method('execute')
            ->with(LanguageResolver::DEFAULT_LANGUAGE, self::API_CONFIG)
            ->willThrowException(new \Exception('error'));

        $this->resultMock->expects($this->once())->method('setData')->with([]);

        $this->assertSame($this->resultMock, $this->model->execute());
    }
}
