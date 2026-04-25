<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Blik;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use PayPro\Przelewy24\Api\Data\BlikAliasInterface;
use PayPro\Przelewy24\Model\Blik\BlikAlias;
use PayPro\Przelewy24\Model\Blik\CreateBlikPaymentToken;
use PHPUnit\Framework\TestCase;

class CreateBlikPaymentTokenTest extends TestCase
{
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentTokenMock;

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
     * @var \PayPro\Przelewy24\Model\Blik\CreateBlikPaymentToken
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $this->paymentTokenFactoryMock = $this->getMockForAbstractClass(PaymentTokenFactoryInterface::class);
        $this->paymentTokenManagementMock = $this->getMockForAbstractClass(PaymentTokenManagementInterface::class);
        $this->paymentTokenRepositoryMock = $this->getMockForAbstractClass(PaymentTokenRepositoryInterface::class);
        $this->model = new CreateBlikPaymentToken(
            $this->paymentTokenFactoryMock,
            $this->paymentTokenManagementMock,
            $this->paymentTokenRepositoryMock,
            new Json()
        );
    }

    public function testExecuteExisting(): void
    {
        $this->paymentTokenManagementMock->expects($this->once())
            ->method('getByGatewayToken')
            ->with('value', 'przelewy24_blik', 1)
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenMock->expects($this->once())->method('setIsActive')->with(true);
        $this->paymentTokenMock->expects($this->once())->method('setIsVisible')->with(true);

        $this->paymentTokenRepositoryMock->expects($this->once())->method('save')->with($this->paymentTokenMock);

        $alias = new BlikAlias('label', 'value');
        $this->assertSame($this->paymentTokenMock, $this->model->execute($alias, 1));
    }

    public function testExecuteNew(): void
    {
        $this->paymentTokenManagementMock->expects($this->once())
            ->method('getByGatewayToken')
            ->with('value', 'przelewy24_blik', 1)
            ->willReturn(null);

        $this->paymentTokenFactoryMock->expects($this->once())
            ->method('create')
            ->with(BlikAliasInterface::VAULT_TOKEN_TYPE)
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenMock->expects($this->once())->method('setGatewayToken')->with('value');
        $this->paymentTokenMock->expects($this->once())
            ->method('setTokenDetails')
            ->with('{"email":"value","label":"label"}');
        $this->paymentTokenMock->expects($this->once())->method('setExpiresAt');

        $alias = new BlikAlias('label', 'value');
        $this->assertSame($this->paymentTokenMock, $this->model->execute($alias, 1));
    }
}
