<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Plugin;

use Magento\Framework\UrlInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Plugin\AdjustAdminOrderViewPaymentButtonsPlugin;
use PHPUnit\Framework\TestCase;

class AdjustAdminOrderViewPaymentButtonsPluginTest extends TestCase
{
    public function testBeforeSetLayoutDifferentPaymentProvider(): void
    {
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())->method('getMethod')->willReturn('banktransfer');

        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('isPaymentReview')->willReturn(true);
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);

        $subjectMock = $this->createMock(View::class);
        $subjectMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $subjectMock->expects($this->never())->method('addButton');

        $urlMock = $this->createMock(UrlInterface::class);

        $model = new AdjustAdminOrderViewPaymentButtonsPlugin($urlMock);
        $model->beforeSetLayout($subjectMock);
    }

    public function testBeforeSetLayoutInvalidState(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('isPaymentReview')->willReturn(false);

        $subjectMock = $this->createMock(View::class);
        $subjectMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $subjectMock->expects($this->never())->method('addButton');

        $urlMock = $this->createMock(UrlInterface::class);

        $model = new AdjustAdminOrderViewPaymentButtonsPlugin($urlMock);
        $model->beforeSetLayout($subjectMock);
    }

    public function testBeforeSetLayoutPrzelewy24(): void
    {
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())->method('getMethod')->willReturn('przelewy24');

        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('isPaymentReview')->willReturn(true);
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);

        $subjectMock = $this->createMock(View::class);
        $subjectMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $subjectMock->expects($this->any())->method('addButton');

        $urlMock = $this->createMock(UrlInterface::class);

        $model = new AdjustAdminOrderViewPaymentButtonsPlugin($urlMock);
        $model->beforeSetLayout($subjectMock);
    }
}
