<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class VerifyTransactionResponseHandler implements HandlerInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $parentTransaction = $payment->getAuthorizationTransaction();

        $payment->setIsTransactionPending(false);
        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);

        if ($parentTransaction) {
            $payment->setTransactionAdditionalInfo(
                Transaction::RAW_DETAILS,
                $parentTransaction->getAdditionalInformation(Transaction::RAW_DETAILS)
            );
        }
    }
}
