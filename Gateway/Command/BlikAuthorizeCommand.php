<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PayPro\Przelewy24\Observer\TokenDataAssignObserver;

class BlikAuthorizeCommand implements CommandInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Model\Blik\BlikAliasResolver
     */
    private \PayPro\Przelewy24\Model\Blik\BlikAliasResolver $blikAliasResolver;

    /**
     * @var \PayPro\Przelewy24\Model\Blik\CreateBlikPaymentToken
     */
    private \PayPro\Przelewy24\Model\Blik\CreateBlikPaymentToken $createBlikPaymentToken;

    /**
     * @var \PayPro\Przelewy24\Model\Vault\SavePaymentToken
     */
    private \PayPro\Przelewy24\Model\Vault\SavePaymentToken $savePaymentToken;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Model\Blik\BlikAliasResolver $blikAliasResolver,
        \PayPro\Przelewy24\Model\Blik\CreateBlikPaymentToken $createBlikPaymentToken,
        \PayPro\Przelewy24\Model\Vault\SavePaymentToken $savePaymentToken
    ) {
        $this->subjectReader = $subjectReader;
        $this->blikAliasResolver = $blikAliasResolver;
        $this->createBlikPaymentToken = $createBlikPaymentToken;
        $this->savePaymentToken = $savePaymentToken;
    }

    /**
     * @param array $commandSubject
     * @return null|void
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $payment->setTransactionId($payment->getAdditionalInformation(TokenDataAssignObserver::SESSION_ID));
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);

        if ($payment->getAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE)) {
            $blikAlias = $this->blikAliasResolver->resolve($commandSubject);

            $paymentToken = $this->createBlikPaymentToken->execute(
                $blikAlias,
                (int) $paymentDO->getOrder()->getCustomerId()
            );
            $this->savePaymentToken->execute($payment, $paymentToken);
        }
    }
}
