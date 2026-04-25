<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Command\DenyPaymentCommand;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\DenyPayment;
use PHPUnit\Framework\TestCase;

class DenyPaymentCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $denyPaymentMock = $this->createMock(DenyPayment::class);
        $configMock = $this->createMock(CommonConfig::class);
        $model = new DenyPaymentCommand(new SubjectReader(), $denyPaymentMock, $configMock);
        $orderAdapterMock = $this->createMock(OrderAdapterInterface::class);
        $orderAdapterMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $paymentMock = $this->createMock(Payment::class);
        $transactionMock = $this->createMock(TransactionInterface::class);
        $paymentMock->expects($this->once())->method('getAuthorizationTransaction')->willReturn($transactionMock);
        $paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDataObjectMock->expects($this->once())->method('getOrder')->willReturn($orderAdapterMock);
        $paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $denyPaymentMock->expects($this->once())->method('execute');

        $model->execute([
            'payment' => $paymentDataObjectMock,
        ]);
    }
}
