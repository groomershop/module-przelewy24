<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Response;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use PayPro\Przelewy24\Model\PaymentLink;
use PayPro\Przelewy24\Model\TransactionUrl;

class RegisterTransactionResponseHandler implements HandlerInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Model\TransactionUrl
     */
    private $transactionUrl;

    /**
     * @var \PayPro\Przelewy24\Model\PaymentLink
     */
    private $paymentLink;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Model\TransactionUrl $transactionUrl,
        \PayPro\Przelewy24\Model\PaymentLink $paymentLink,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->transactionUrl = $transactionUrl;
        $this->paymentLink = $paymentLink;
        $this->logger = $logger;
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $token = $this->subjectReader->readTransactionToken($response);
        if (!$token) {
            $this->logger->error('Przelewy24: can\'t read transaction token', ['response' => $response]);
            throw new CommandException(__('Transaction has been declined. Please try again later.'));
        }

        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);

        $payment->setAdditionalInformation(
            TransactionUrl::KEY,
            $this->transactionUrl->get($token, $this->subjectReader->readOrderStoreId($handlingSubject))
        );

        if (!$payment->hasAdditionalInformation(PaymentLink::LABEL)) {
            $payment->setAdditionalInformation(
                PaymentLink::LABEL,
                (string) __('<a href="%1">Click here</a>', $this->paymentLink->execute($payment))
            );
        }
    }
}
