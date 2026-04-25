<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Vault;

use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PayPro\Przelewy24\Model\Vault\SavePaymentToken;
use PHPUnit\Framework\TestCase;

class SavePaymentTokenTest extends TestCase
{
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentExtensionFactoryMock;

    /**
     * @var \PayPro\Przelewy24\Model\Vault\SavePaymentToken
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMock = $this->getMockForAbstractClass(OrderPaymentInterface::class);
        $this->paymentExtensionFactoryMock = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SavePaymentToken($this->paymentExtensionFactoryMock);
    }

    public function testExecute(): void
    {
        $paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $paymentExtensionMock = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->addMethods(['setVaultPaymentToken'])
            ->getMockForAbstractClass();
        $this->paymentExtensionFactoryMock->expects($this->once())->method('create')->willReturn($paymentExtensionMock);
        $paymentExtensionMock->expects($this->once())->method('setVaultPaymentToken')->with($paymentTokenMock);

        $this->model->execute($this->paymentMock, $paymentTokenMock);
    }

    public function testExecuteEmptyToken(): void
    {
        $this->paymentMock->expects($this->never())->method('getExtensionAttributes');
        $this->paymentExtensionFactoryMock->expects($this->never())->method('create');

        $this->model->execute($this->paymentMock, null);
    }
}
