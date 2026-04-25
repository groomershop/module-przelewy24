<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Controller\Adminhtml\Payment\Review;
use PayPro\Przelewy24\Model\UpdatePaymentByTransactions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReviewTest extends TestCase
{
    private const ORDER_ID = '100';

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \PayPro\Przelewy24\Model\UpdatePaymentByTransactions|\PHPUnit\Framework\MockObject\MockObject
     */
    private $updatePaymentByTransactionsMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \PayPro\Przelewy24\Controller\Adminhtml\Payment\Review
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $requestMock = $this->createMock(RequestInterface::class);

        $resultMock = $this->createMock(Redirect::class);
        $requestMock->expects($this->once())->method('getParam')->with('order_id')->willReturn(self::ORDER_ID);

        $redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())->method('setPath');

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResultRedirectFactory')->willReturn($redirectFactoryMock);
        $contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->updatePaymentByTransactionsMock = $this->createMock(UpdatePaymentByTransactions::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);

        $this->model = new Review(
            $contextMock,
            $this->orderRepositoryMock,
            $this->updatePaymentByTransactionsMock,
            $this->loggerMock
        );
    }

    public function testExecute(): void
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(100)
            ->willReturn($this->orderMock);

        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');
        $this->updatePaymentByTransactionsMock->expects($this->once())->method('execute')->with($this->paymentMock);
        $this->orderRepositoryMock->expects($this->once())->method('save')->with($this->orderMock);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');

        $this->assertInstanceOf(ResultInterface::class, $this->model->execute());
    }

    public function testExecuteInvalidPaymentMethod(): void
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(100)
            ->willReturn($this->orderMock);

        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('banktransfer');

        $this->updatePaymentByTransactionsMock->expects($this->never())->method('execute');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->assertInstanceOf(ResultInterface::class, $this->model->execute());
    }

    public function testExecuteException(): void
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(100)
            ->willThrowException(new NotFoundException(__('Not found')));

        $this->updatePaymentByTransactionsMock->expects($this->never())->method('execute');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->assertInstanceOf(ResultInterface::class, $this->model->execute());
    }

    public function testExecuteFatalError(): void
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(100)
            ->willThrowException(new \Exception('Fatal error'));

        $this->updatePaymentByTransactionsMock->expects($this->never())->method('execute');
        $this->loggerMock->expects($this->once())->method('error');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->assertInstanceOf(ResultInterface::class, $this->model->execute());
    }
}
