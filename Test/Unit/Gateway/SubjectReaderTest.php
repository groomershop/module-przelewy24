<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class SubjectReaderTest extends TestCase
{
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var array
     */
    private $subject;

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->subject = [
            'payment' => $this->paymentMock,
            'amount' => '10.5000',
            'response' => [
                'data' => ['responseData' => 1],
            ],
        ];

        $this->model = new SubjectReader();
    }

    public function testReadOrderStoreId(): void
    {
        $orderAdapterMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderAdapterMock);
        $orderAdapterMock->expects($this->once())->method('getStoreId')->willReturn('1');

        $this->assertEquals(1, $this->model->readOrderStoreId($this->subject));
    }

    public function testReadAmount(): void
    {
        $this->assertEquals('10.5000', $this->model->readAmount($this->subject));
    }

    public function testReadCurrency(): void
    {
        $orderAdapterMock = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderAdapterMock);
        $orderAdapterMock->expects($this->once())->method('getCurrencyCode')->willReturn('PLN');

        $this->assertEquals('PLN', $this->model->readCurrency($this->subject));
    }

    public function testReadTransactionToken(): void
    {
        $this->assertEquals('TOKEN', $this->model->readTransactionToken(['data' => ['token' => 'TOKEN']]));
    }

    public function testReadResponse(): void
    {
        $this->assertEquals(['responseData' => 1], $this->model->readResponse($this->subject));
    }

    public function testReadResponseError(): void
    {
        $this->assertEquals('error message', $this->model->readResponseError([
            'response' => [
                'error' => ['message' => 'error message'],
            ],
        ]));

        $this->assertEquals('error message', $this->model->readResponseError([
            'response' => ['error' => 'error message'],
        ]));
    }

    public function testReadEmptyResponseError(): void
    {
        $this->assertNull($this->model->readResponseError($this->subject));
    }

    public function testReadPayment(): void
    {
        $this->assertSame($this->paymentMock, $this->model->readPayment($this->subject));
    }

    public function testReadOrderIncrementId(): void
    {
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $paymentModelMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn('00000001');

        $this->assertEquals('00000001', $this->model->readOrderIncrementId($this->subject));
    }
}
