<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PayPro\Przelewy24\Model\Card\CardDetails;
use PayPro\Przelewy24\Observer\CardDataAssignObserver;

class CardAuthorizeCommand implements CommandInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Model\Card\CreateCardPaymentToken
     */
    private $createPaymentToken;

    /**
     * @var \PayPro\Przelewy24\Model\Vault\SavePaymentToken
     */
    private $savePaymentToken;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Model\Card\CreateCardPaymentToken $createPaymentToken,
        \PayPro\Przelewy24\Model\Vault\SavePaymentToken $savePaymentToken
    ) {
        $this->subjectReader = $subjectReader;
        $this->createPaymentToken = $createPaymentToken;
        $this->savePaymentToken = $savePaymentToken;
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

        $payment->setTransactionId($payment->getAdditionalInformation(CardDataAssignObserver::SESSION_ID));
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);

        if ($this->isPaymentTokenSaveRequired($payment)) {
            $cardDetails = new CardDetails(
                (string) $payment->getAdditionalInformation(CardDataAssignObserver::CARD_TYPE),
                (string) $payment->getAdditionalInformation(CardDataAssignObserver::CARD_MASK),
                (string) $payment->getAdditionalInformation(CardDataAssignObserver::CARD_DATE)
            );
            $paymentToken = $this->createPaymentToken->execute(
                (string) $payment->getAdditionalInformation(CardDataAssignObserver::REF_ID),
                $cardDetails,
                (int) $paymentDO->getOrder()->getCustomerId()
            );

            $this->savePaymentToken->execute($payment, $paymentToken);
        }
    }

    private function isPaymentTokenSaveRequired(InfoInterface $paymentInfo): bool
    {
        return $paymentInfo->getAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE)
            && $paymentInfo->getAdditionalInformation(CardDataAssignObserver::REF_ID);
    }
}
