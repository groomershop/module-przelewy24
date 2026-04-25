<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PayPro\Przelewy24\Controller\Payment\Redirect;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
    private const REDIRECT_URL = 'https://przelewy24.pl';

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $redirectFactoryMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultMock;

    /**
     * @var \PayPro\Przelewy24\Controller\Payment\Redirect
     */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->paymentMock = $this->createMock(OrderPaymentInterface::class);
        $this->resultMock = $this->createMock(ResultRedirect::class);
        $this->redirectFactoryMock->expects($this->once())->method('create')->willReturn($this->resultMock);
        $this->checkoutSessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->orderMock);

        $this->controller = new Redirect(
            $this->redirectFactoryMock,
            $this->checkoutSessionMock,
            $this->messageManagerMock,
            $this->loggerMock
        );
    }

    public function testExecute(): void
    {
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getAdditionalInformation')->willReturn(self::REDIRECT_URL);
        $this->resultMock->expects($this->once())->method('setUrl')->with(self::REDIRECT_URL);

        $this->assertEquals($this->resultMock, $this->controller->execute());
    }

    public function testExecuteException(): void
    {
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn(null);
        $this->loggerMock->expects($this->once())->method('error');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->assertEquals($this->resultMock, $this->controller->execute());
    }
}
