<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

class ValidatorTestCase extends TestCase
{
    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentDOMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultFactoryMock = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockForAbstractClass(ResultInterface::class);

        $this->orderMock = $this->createMock(Order::class);
        $this->orderMock->expects($this->any())->method('getIncrementId')->willReturn('000000001');
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentMock->expects($this->any())->method('getTransactionId')->willReturn('uuid');
        $this->paymentMock->expects($this->any())->method('getOrder')->willReturn($this->orderMock);
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentDOMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
    }
}
