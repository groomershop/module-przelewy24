<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPro\Przelewy24\Gateway\Request\BlikVaultRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class VaultRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $paymentTokenManagementMock = $this->getMockForAbstractClass(PaymentTokenManagementInterface::class);
        $paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $paymentMock =  $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->once())->method('getPayment')->willReturn($paymentModelMock);
        $paymentModelMock->expects($this->exactly(2))
            ->method('getAdditionalInformation')
            ->withConsecutive(['public_hash'], ['customer_id'])
            ->willReturnOnConsecutiveCalls('hash', 1);
        $paymentTokenManagementMock->expects($this->once())
            ->method('getByPublicHash')
            ->with('hash', 1)
            ->willReturn($paymentTokenMock);
        $paymentTokenMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $paymentTokenMock->expects($this->once())->method('getGatewayToken')->willReturn('payment_token');

        $model = new BlikVaultRequestBuilder(new SubjectReader(), $paymentTokenManagementMock);

        $this->assertEquals([
            'methodRefId' => 'payment_token',
        ], $model->build(['payment' => $paymentMock]));
    }
}
