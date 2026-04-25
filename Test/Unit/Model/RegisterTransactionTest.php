<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\ApiClientInterfaceFactory;
use PayPro\Przelewy24\Model\Api\ApiConfig;
use PayPro\Przelewy24\Model\TransactionPayloadTransformer;
use PayPro\Przelewy24\Model\RegisterTransaction;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RegisterTransactionTest extends TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $maskedQuoteIdToQuoteIdMock;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClientFactoryMock;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiConfigMock;

    /**
     * @var \PayPro\Przelewy24\Model\TransactionPayloadTransformer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartToTransactionPayloadMock;

    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentLoggerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClientMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteMock;

    /**
     * @var \PayPro\Przelewy24\Model\RegisterTransaction
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->maskedQuoteIdToQuoteIdMock = $this->getMockForAbstractClass(MaskedQuoteIdToQuoteIdInterface::class);
        $this->apiClientFactoryMock = $this->getMockBuilder(ApiClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiConfigMock = $this->getMockBuilder(ApiConfig::class)->disableOriginalConstructor()->getMock();
        $this->cartToTransactionPayloadMock = $this->getMockBuilder(TransactionPayloadTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentLoggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $this->paymentLoggerMock->expects($this->once())->method('debug');
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->apiClientMock = $this->getMockForAbstractClass(ApiClientInterface::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();

        $clientConfig = [ApiConfig::URL => 'url', ApiConfig::USERNAME => 'user', ApiConfig::PASSWORD => 'pass'];
        $this->apiClientFactoryMock->expects($this->once())
            ->method('create')
            ->with($clientConfig)
            ->willReturn($this->apiClientMock);

        $this->apiConfigMock->expects($this->once())->method('get')->willReturn($clientConfig);

        $this->model = new RegisterTransaction(
            $this->cartRepositoryMock,
            $this->maskedQuoteIdToQuoteIdMock,
            $this->apiClientFactoryMock,
            $this->apiConfigMock,
            $this->cartToTransactionPayloadMock,
            $this->paymentLoggerMock,
            $this->loggerMock,
            'clientName',
            'paymentMethodName'
        );
    }

    public function testExecute(): void
    {
        $transactionPayload = [
            'sessionId' => 'uuid',
            'sign' => 'signature',
        ];
        $this->cartRepositoryMock->expects($this->once())->method('getActive')->with(1)->willReturn($this->quoteMock);
        $this->maskedQuoteIdToQuoteIdMock->expects($this->never())->method('execute');
        $this->cartToTransactionPayloadMock->expects($this->once())
            ->method('fromCart')
            ->with($this->quoteMock)
            ->willReturn(array_merge($transactionPayload, ['methodRefId' => 'method']));

        $this->apiClientMock->expects($this->once())->method('registerTransaction')->willReturn([
            'data' => [
                'token' => 'TOKEN',
            ],
        ]);

        $this->assertEquals([
            RegisterTransaction::RESPONSE => ['token' => 'TOKEN'],
            RegisterTransaction::PAYLOAD => array_merge($transactionPayload, ['methodRefId' => 'method']),
        ], $this->model->execute('1', ['methodRefId' => 'method']));
    }

    public function testExecuteWithMaskedQuoteId(): void
    {
        $transactionPayload = [
            'sessionId' => 'uuid',
            'sign' => 'signature',
        ];
        $this->cartRepositoryMock->expects($this->once())->method('getActive')->with(1)->willReturn($this->quoteMock);
        $this->maskedQuoteIdToQuoteIdMock->expects($this->once())->method('execute')->willReturn(1);
        $this->cartToTransactionPayloadMock->expects($this->once())
            ->method('fromCart')
            ->with($this->quoteMock)
            ->willReturn($transactionPayload);

        $this->apiClientMock->expects($this->once())->method('registerTransaction')->willReturn([
            'data' => [
                'token' => 'TOKEN',
            ],
        ]);

        $this->assertEquals([
            RegisterTransaction::RESPONSE => ['token' => 'TOKEN'],
            RegisterTransaction::PAYLOAD => $transactionPayload,
        ], $this->model->execute('maskedQuoteId'));
    }

    public function testExecuteApiError(): void
    {
        $this->expectException(LocalizedException::class);
        $transactionPayload = [
            'sessionId' => 'uuid',
            'sign' => 'signature',
        ];
        $this->cartRepositoryMock->expects($this->once())->method('getActive')->with(1)->willReturn($this->quoteMock);
        $this->cartToTransactionPayloadMock->expects($this->once())
            ->method('fromCart')
            ->with($this->quoteMock)
            ->willReturn($transactionPayload);

        $this->apiClientMock->expects($this->once())->method('registerTransaction')
            ->with($transactionPayload)
            ->willThrowException(new \Exception());

        $this->model->execute('1');
    }
}
