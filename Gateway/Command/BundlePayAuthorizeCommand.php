<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use PayPro\Przelewy24\Observer\TokenDataAssignObserver;

class BundlePayAuthorizeCommand implements CommandInterface
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
     * @param array $commandSubject
     * @return null|void
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $payment->setTransactionId($payment->getAdditionalInformation(TokenDataAssignObserver::SESSION_ID));
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
    }
}
