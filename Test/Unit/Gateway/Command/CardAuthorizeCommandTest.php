<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PayPro\Przelewy24\Gateway\Command\CardAuthorizeCommand;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\Card\CardDetails;
use PayPro\Przelewy24\Model\Card\CreateCardPaymentToken;
use PayPro\Przelewy24\Model\Vault\SavePaymentToken;
use PayPro\Przelewy24\Observer\CardDataAssignObserver;
use PHPUnit\Framework\TestCase;

class CardAuthorizeCommandTest extends TestCase
{
    const SESSION_ID = 'session-uuid';
    const CARD_TYPE = 'visa';
    const CARD_MASK = 'XXXX XXXX XXXX 1111';
    const CARD_DATE = '122025';
    const REF_ID = 'refid-uuid';
    const CUSTOMER_ID = 1;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentModelMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \PayPro\Przelewy24\Model\Card\CreateCardPaymentToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $createPaymentTokenMock;

    /**
     * @var \PayPro\Przelewy24\Model\Vault\SavePaymentToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $savePaymentTokenMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Command\CardAuthorizeCommand
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentModelMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock->expects($this->once())->method('getPayment')->willReturn($this->paymentModelMock);
        $this->paymentModelMock->expects($this->once())->method('setIsTransactionPending')->with(true);
        $this->paymentModelMock->expects($this->once())->method('setIsTransactionClosed')->with(false);

        $this->createPaymentTokenMock = $this->getMockBuilder(CreateCardPaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->savePaymentTokenMock = $this->getMockBuilder(SavePaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CardAuthorizeCommand(
            new SubjectReader(),
            $this->createPaymentTokenMock,
            $this->savePaymentTokenMock
        );
    }

    public function testExecute(): void
    {
        $this->paymentModelMock->expects($this->exactly(2))
            ->method('getAdditionalInformation')
            ->withConsecutive([CardDataAssignObserver::SESSION_ID], [VaultConfigProvider::IS_ACTIVE_CODE])
            ->willReturnOnConsecutiveCalls(self::SESSION_ID, false);
        $this->paymentModelMock->expects($this->once())->method('setTransactionId')->with(self::SESSION_ID);
        $this->createPaymentTokenMock->expects($this->never())->method('execute');
        $this->savePaymentTokenMock->expects($this->never())->method('execute');

        $this->model->execute([
            'payment' => $this->paymentMock,
        ]);
    }

    public function testExecuteWithToken(): void
    {
        $paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $orderMock = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $this->paymentModelMock->expects($this->exactly(7))
            ->method('getAdditionalInformation')
            ->withConsecutive(
                [CardDataAssignObserver::SESSION_ID],
                [VaultConfigProvider::IS_ACTIVE_CODE],
                [CardDataAssignObserver::REF_ID],
                [CardDataAssignObserver::CARD_TYPE],
                [CardDataAssignObserver::CARD_MASK],
                [CardDataAssignObserver::CARD_DATE],
                [CardDataAssignObserver::REF_ID]
            )->willReturnOnConsecutiveCalls(
                self::SESSION_ID,
                true,
                self::REF_ID,
                self::CARD_TYPE,
                self::CARD_MASK,
                self::CARD_DATE,
                self::REF_ID
            );
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getCustomerId')->willReturn(self::CUSTOMER_ID);
        $this->paymentModelMock->expects($this->once())->method('setTransactionId')->with(self::SESSION_ID);

        $cardDetails = new CardDetails(
            self::CARD_TYPE,
            self::CARD_MASK,
            self::CARD_DATE
        );

        $this->createPaymentTokenMock->expects($this->once())
            ->method('execute')
            ->with(self::REF_ID, $cardDetails, self::CUSTOMER_ID)
            ->willReturn($paymentTokenMock);

        $this->savePaymentTokenMock->expects($this->once())
            ->method('execute')
            ->with($this->paymentModelMock, $paymentTokenMock);

        $this->model->execute([
            'payment' => $this->paymentMock,
        ]);
    }
}
