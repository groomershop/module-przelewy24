<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Adminhtml\Test;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\ApiClientInterfaceFactory;
use PayPro\Przelewy24\Controller\Adminhtml\Test\Access;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PHPUnit\Framework\TestCase;

class AccessTest extends TestCase
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
     * @var \PayPro\Przelewy24\Api\ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClientMock;

    /**
     * @var \PayPro\Przelewy24\Controller\Adminhtml\Test\Access
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $apiClientFactoryMock = $this->getMockBuilder(ApiClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiConfigMock = $this->getMockBuilder(ApiConfig::class)->disableOriginalConstructor()->getMock();
        $resultFactoryMock = $this->getMockBuilder(ResultFactory::class)->disableOriginalConstructor()->getMock();
        $this->resultMock = $this->getMockBuilder(Json::class)->disableOriginalConstructor()->getMock();
        $this->apiClientMock = $this->getMockForAbstractClass(ApiClientInterface::class);
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $contextMock->expects($this->once())->method('getResultFactory')->willReturn($resultFactoryMock);
        $requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['scope_type', 'default'], ['scope_id'])
            ->willReturnOnConsecutiveCalls('store', '1');

        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);

        $apiConfigMock->expects($this->once())->method('get')->willReturn(self::API_CONFIG);

        $apiClientFactoryMock->expects($this->once())
            ->method('create')
            ->with(self::API_CONFIG)
            ->willReturn($this->apiClientMock);

        $this->model = new Access($contextMock, $apiClientFactoryMock, $apiConfigMock);
    }

    public function testExecute(): void
    {
        $this->apiClientMock->expects($this->once())->method('testAccess')->willReturn(['data' => 1]);
        $this->resultMock->expects($this->once())->method('setData')->with(['data' => 1]);

        $this->assertSame($this->resultMock, $this->model->execute());
    }

    public function testExecuteException(): void
    {
        $this->apiClientMock->expects($this->once())->method('testAccess')->willThrowException(new \Exception('error'));
        $this->resultMock->expects($this->once())->method('setData')->with(['error' => 'error']);

        $this->assertSame($this->resultMock, $this->model->execute());
    }
}
