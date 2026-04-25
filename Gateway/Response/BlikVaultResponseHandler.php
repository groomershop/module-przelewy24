<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class BlikVaultResponseHandler implements HandlerInterface
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

    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $payment->setTransactionId($response['sessionId']);
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
    }
}
