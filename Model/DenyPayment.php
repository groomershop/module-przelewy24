<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class DenyPayment
{
    /**
     * @var \PayPro\Przelewy24\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentMade
     */
    private $isPaymentMade;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired
     */
    private $isPaymentExpired;

    public function __construct(
        \PayPro\Przelewy24\Api\TransactionRepositoryInterface $transactionRepository,
        \PayPro\Przelewy24\Model\IsPaymentMade $isPaymentMade,
        \PayPro\Przelewy24\Model\IsPaymentExpired $isPaymentExpired
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->isPaymentMade = $isPaymentMade;
        $this->isPaymentExpired = $isPaymentExpired;
    }

    public function execute(PaymentDataObjectInterface $paymentDataObject, ApiTransaction $transaction): void
    {
        $magentoTransaction = $this->transactionRepository->get($transaction);
        if ($magentoTransaction->getIsClosed()) {
            throw new LocalizedException(
                __('Transaction %1 is already closed', $transaction->getId())
            );
        }

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDataObject->getPayment();

        if ($this->isPaymentMade->execute($transaction->getSessionId(), (int) $payment->getOrder()->getStoreId())) {
            throw new LocalizedException(__('Payment made, it can\'t be denied.'));
        }

        if (!$this->isPaymentExpired->execute($payment)) {
            throw new LocalizedException(__('Payment in progress, it can\'t be denied'));
        }

        $magentoTransaction->setIsClosed(1);
        $payment->setIsTransactionDenied(true);
    }
}
