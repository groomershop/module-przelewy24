<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Payment;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Controller\Payment\Pay;
use PayPro\Przelewy24\Model\RenewTransaction;
use PayPro\Przelewy24\Model\TransactionUrl;
use PHPUnit\Framework\TestCase;

class PayTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \PayPro\Przelewy24\Model\RenewTransaction|\PHPUnit\Framework\MockObject\MockObject
     */
    private $renewTransactionMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    private $redirect;

    /**
     * @var \PayPro\Przelewy24\Controller\Payment\Pay
     */
    private $controller;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->renewTransactionMock = $this->createMock(RenewTransaction::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->expects($this->once())->method('create')->willReturn($this->redirect);

        $this->controller = new Pay(
            $this->requestMock,
            $this->renewTransactionMock,
            $redirectFactory,
            $this->messageManagerMock
        );
    }

    public function testExecute(): void
    {
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(TransactionUrl::KEY)
            ->willReturn('p24_url');
        $this->redirect->expects($this->once())->method('setUrl')->with('p24_url');
        $this->requestMock->expects($this->once())->method('getParam')->with('id')->willReturn('uuid');
        $this->renewTransactionMock->expects($this->once())
            ->method('execute')
            ->with('uuid')
            ->willReturn($paymentMock);

        $this->assertEquals($this->redirect, $this->controller->execute());
    }

    public function testExecuteNoId(): void
    {
        $this->redirect->expects($this->never())->method('setUrl');
        $this->redirect->expects($this->once())->method('setPath')->with('/')->willReturnSelf();
        $this->requestMock->expects($this->once())->method('getParam')->with('id')->willReturn(null);
        $this->renewTransactionMock->expects($this->never())->method('execute');

        $this->assertEquals($this->redirect, $this->controller->execute());
    }

    public function testExecuteError(): void
    {
        $this->redirect->expects($this->never())->method('setUrl');
        $this->redirect->expects($this->once())->method('setPath')->with('/')->willReturnSelf();
        $this->requestMock->expects($this->once())->method('getParam')->with('id')->willReturn('uuid');
        $this->renewTransactionMock->expects($this->once())
            ->method('execute')
            ->with('uuid')
            ->willThrowException(new \Exception('Error'));

        $this->assertEquals($this->redirect, $this->controller->execute());
    }

    public function testExecutePaymentError(): void
    {
        $this->redirect->expects($this->never())->method('setUrl');
        $this->redirect->expects($this->once())->method('setPath')->with('/')->willReturnSelf();
        $this->requestMock->expects($this->once())->method('getParam')->with('id')->willReturn('uuid');
        $this->renewTransactionMock->expects($this->once())
            ->method('execute')
            ->with('uuid')
            ->willThrowException(new \Magento\Framework\Exception\PaymentException(__('Error')));

        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->assertEquals($this->redirect, $this->controller->execute());
    }
}
