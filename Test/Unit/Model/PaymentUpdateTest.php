<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\Config\ScopeInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Model\PaymentUpdate;
use PayPro\Przelewy24\Model\Processor\InvoiceProcessor;
use PayPro\Przelewy24\Model\UpdatePaymentByTransactions;
use PHPUnit\Framework\TestCase;

class PaymentUpdateTest extends TestCase
{
    public function testExecute(): void
    {
        $orderId = 10;

        $areaListMock = $this->createMock(AreaList::class);
        $areaListMock->expects($this->once())->method('getCodes')->willReturn([Area::AREA_ADMINHTML]);

        $scope = $this->getMockForAbstractClass(ScopeInterface::Class);
        $state = new \Magento\Framework\App\State($scope);
        $stateReflection = new \ReflectionClass($state);
        $property = $stateReflection->getProperty('areaList');
        $property->setAccessible(true);
        $property->setValue($state, $areaListMock);

        $paymentMock = $this->createMock(Payment::class);

        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);

        $orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $invoiceProcessorMock = $this->createMock(InvoiceProcessor::class);
        $updatePaymentByTransactionsMock = $this->createMock(UpdatePaymentByTransactions::class);

        $orderRepositoryMock->expects($this->once())->method('get')->with($orderId)->willReturn($orderMock);
        $orderRepositoryMock->expects($this->once())->method('save')->with($orderMock);

        $invoiceProcessorMock->expects($this->once())->method('process')->with($paymentMock);

        $updatePaymentByTransactionsMock->expects($this->once())->method('execute')->with($paymentMock);

        $model = new PaymentUpdate(
            $state,
            $orderRepositoryMock,
            $invoiceProcessorMock,
            $updatePaymentByTransactionsMock
        );

        $model->execute($orderId);
    }
}
