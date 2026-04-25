<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Http;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Gateway\Http\TransferFactory;
use PayPro\Przelewy24\Gateway\Request\StoreIdRequestBuilder;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PHPUnit\Framework\TestCase;

class TransferFactoryTest extends TestCase
{
    private const URL = 'https://sandbox.przelewy24.pl';
    private const USERNAME = 'username';
    private const PASSWORD = 'password';

    /**
     * @var \Magento\Payment\Gateway\Http\TransferInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transferMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transferBuilderMock;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiConfigMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Http\TransferFactory
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transferMock = $this->getMockForAbstractClass(TransferInterface::class);
        $this->transferBuilderMock = $this->getMockBuilder(TransferBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiConfigMock = $this->getMockBuilder(ApiConfig::class)->disableOriginalConstructor()->getMock();

        $this->model = new TransferFactory($this->transferBuilderMock, $this->apiConfigMock);
    }

    public function testCreate(): void
    {
        $request = ['data' => 'a', StoreIdRequestBuilder::STORE_ID => 1];

        $this->transferBuilderMock->expects($this->once())->method('setUri')->with(self::URL)->willReturnSelf();
        $this->transferBuilderMock->expects($this->once())
            ->method('setAuthUsername')
            ->with(self::USERNAME)
            ->willReturnSelf();
        $this->transferBuilderMock->expects($this->once())
            ->method('setAuthPassword')
            ->with(self::PASSWORD)
            ->willReturnSelf();
        $this->transferBuilderMock->expects($this->once())->method('setBody')->with(['data' => 'a'])->willReturnSelf();
        $this->transferBuilderMock->expects($this->once())->method('build')->willReturn($this->transferMock);

        $this->apiConfigMock->expects($this->once())->method('get')->with(ScopeInterface::SCOPE_STORE, 1)->willReturn([
            ApiConfig::URL => self::URL,
            ApiConfig::USERNAME => self::USERNAME,
            ApiConfig::PASSWORD => self::PASSWORD,
        ]);

        $this->assertEquals($this->transferMock, $this->model->create($request));
    }

    public function testMissingStoreId(): void
    {
        $this->expectException(CommandException::class);
        $request = ['data' => 'a'];
        $this->apiConfigMock->expects($this->never())->method('get');
        $this->transferBuilderMock->expects($this->never())->method('build');

        $this->model->create($request);
    }
}
