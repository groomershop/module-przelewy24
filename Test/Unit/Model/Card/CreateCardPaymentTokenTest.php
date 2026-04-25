<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Card;

use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPro\Przelewy24\Model\Card\CardDetails;
use PayPro\Przelewy24\Model\Card\CreateCardPaymentToken;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

class CreateCardPaymentTokenTest extends TestCase
{
    const REF_ID = 'ref_id';
    const CUSTOMER_ID = 1;

    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentTokenFactoryMock;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentTokenManagementMock;

    /**
     * @var \Magento\Vault\Api\PaymentTokenRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentTokenRepositoryMock;

    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentTokenMock;

    /**
     * @var \PayPro\Przelewy24\Model\Card\CardDetails
     */
    private $cardDetails;

    /**
     * @var \PayPro\Przelewy24\Model\Card\CreateCardPaymentToken
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentTokenFactoryMock = $this->getMockForAbstractClass(PaymentTokenFactoryInterface::class);
        $this->paymentTokenManagementMock = $this->getMockForAbstractClass(PaymentTokenManagementInterface::class);
        $this->paymentTokenRepositoryMock = $this->getMockForAbstractClass(PaymentTokenRepositoryInterface::class);
        $this->paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);

        $this->cardDetails = new CardDetails('visa', 'XXXX XXXX XXXX 1111', '122025');

        $this->model = new CreateCardPaymentToken(
            $this->paymentTokenFactoryMock,
            $this->paymentTokenManagementMock,
            $this->paymentTokenRepositoryMock
        );
    }

    public function testExecute(): void
    {
        $this->paymentTokenManagementMock->expects($this->once())
            ->method('getByGatewayToken')
            ->with(self::REF_ID, ConfigProvider::CARD_CODE, self::CUSTOMER_ID)
            ->willReturn(null);

        $this->paymentTokenFactoryMock->expects($this->once())
            ->method('create')
            ->with(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD)
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenMock->expects($this->once())->method('setGatewayToken')->with(self::REF_ID);
        $this->paymentTokenMock->expects($this->once())->method('setExpiresAt')->with('2026-01-01 00:00:00');
        $this->paymentTokenMock->expects($this->once())
            ->method('setTokenDetails')
            ->with('{"type":"VI","maskedCC":"1111","expirationDate":"12\/2025"}');
        $this->paymentTokenMock->expects($this->once())->method('getEntityId')->willReturn(null);
        $this->paymentTokenRepositoryMock->expects($this->never())->method('save');

        $this->assertEquals(
            $this->paymentTokenMock,
            $this->model->execute(self::REF_ID, $this->cardDetails, self::CUSTOMER_ID)
        );
    }

    public function testExecuteWithExistingToken(): void
    {
        $this->paymentTokenManagementMock->expects($this->once())
            ->method('getByGatewayToken')
            ->with(self::REF_ID, ConfigProvider::CARD_CODE, self::CUSTOMER_ID)
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenFactoryMock->expects($this->never())->method('create');
        $this->paymentTokenMock->expects($this->once())->method('setGatewayToken')->with(self::REF_ID);
        $this->paymentTokenMock->expects($this->once())->method('setExpiresAt')->with('2026-01-01 00:00:00');
        $this->paymentTokenMock->expects($this->once())
            ->method('setTokenDetails')
            ->with('{"type":"VI","maskedCC":"1111","expirationDate":"12\/2025"}');
        $this->paymentTokenMock->expects($this->once())->method('getEntityId')->willReturn('1');
        $this->paymentTokenMock->expects($this->once())->method('setIsActive')->with(true);
        $this->paymentTokenMock->expects($this->once())->method('setIsVisible')->with(true);
        $this->paymentTokenRepositoryMock->expects($this->once())->method('save')->with($this->paymentTokenMock);

        $this->assertEquals(
            $this->paymentTokenMock,
            $this->model->execute(self::REF_ID, $this->cardDetails, self::CUSTOMER_ID)
        );
    }
}
