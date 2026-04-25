<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Gateway\Response\RegisterTransactionResponseHandler;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\PaymentLink;
use PayPro\Przelewy24\Model\TransactionUrl;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RegisterTransactionResponseHandlerTest extends TestCase
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    /**
     * @var \PayPro\Przelewy24\Model\TransactionUrl|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionUrlMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentModelMock;

    /**
     * @var \PayPro\Przelewy24\Model\PaymentLink|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentLinkMock;

    /**
     * @var \PayPro\Przelewy24\Gateway\Response\RegisterTransactionResponseHandler
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectReaderMock = $this->createMock(SubjectReader::class);
        $this->transactionUrlMock = $this->createMock(TransactionUrl::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->paymentMock =  $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentModelMock = $this->createMock(Payment::class);

        $this->subjectReaderMock->expects($this->once())->method('readPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('getPayment')->willReturn($this->paymentModelMock);

        $this->paymentLinkMock = $this->createMock(PaymentLink::class);

        $this->model = new RegisterTransactionResponseHandler(
            $this->subjectReaderMock,
            $this->transactionUrlMock,
            $this->paymentLinkMock,
            $this->loggerMock
        );
    }

    public function testHandle(): void
    {
        $paymentLink = 'https://magento.test';

        $this->subjectReaderMock->expects($this->once())->method('readTransactionToken')->willReturn('TOKEN');
        $this->transactionUrlMock->expects($this->once())->method('get')->willReturn('TRANSACTION_URL');
        $this->paymentLinkMock->expects($this->once())->method('execute')->willReturn($paymentLink);

        $this->paymentModelMock->expects($this->once())->method('setIsTransactionPending')->with(true);
        $this->paymentModelMock->expects($this->once())->method('setIsTransactionClosed')->with(false);
        $this->paymentModelMock->expects($this->exactly(2))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [TransactionUrl::KEY, 'TRANSACTION_URL'],
                [PaymentLink::LABEL, (string) __('<a href="%1">Click here</a>', $paymentLink)]
            );

        $this->model->handle([], []);
    }

    public function testHandleEmptyToken(): void
    {
        $this->expectException(CommandException::class);
        $this->loggerMock->expects($this->once())->method('error');
        $this->subjectReaderMock->expects($this->once())->method('readTransactionToken')->willReturn(null);
        $this->model->handle([], []);
    }
}
