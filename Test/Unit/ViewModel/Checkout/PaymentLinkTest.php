<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\ViewModel\Checkout;

use PayPro\Przelewy24\Controller\Payment\Redirect;
use PayPro\Przelewy24\ViewModel\Checkout\PaymentLink;
use PHPUnit\Framework\TestCase;

class PaymentLinkTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var \PayPro\Przelewy24\ViewModel\Checkout\PaymentLink
     */
    private $model;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->urlMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->paymentMock = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $this->checkoutSessionMock->expects($this->any())->method('getLastRealOrder')->willReturn($this->orderMock);

        $this->model = new PaymentLink(
            $this->requestMock,
            $this->checkoutSessionMock,
            $this->urlMock,
            $this->loggerMock
        );
    }

    public function testIsRedirectFailure(): void
    {
        $this->requestMock->expects($this->once())->method('getParam')->with(Redirect::FAILURE_PARAM)->willReturn('1');

        $this->assertTrue($this->model->isRedirectFailure());
    }

    public function testGetPaymentLink(): void
    {
    }

    public function testGetPaymentLinkException(): void
    {
        $this->loggerMock->expects($this->once())->method('error');

        $this->assertNull($this->model->getPaymentLink());
    }

    public function testGetPaymentLinkWhenInvisible(): void
    {
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getMethod')->willReturn('different_method');
        $this->assertNull($this->model->getPaymentLink());
    }
}
