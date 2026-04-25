<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class PaymentProcessor
{
    /**
     * @var \PayPro\Przelewy24\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var \PayPro\Przelewy24\Model\ClosePayment
     */
    private $closePayment;

    public function __construct(
        \PayPro\Przelewy24\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Api\OrderPaymentRepositoryInterface $paymentRepository,
        \PayPro\Przelewy24\Model\ClosePayment $closePayment
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->paymentRepository = $paymentRepository;
        $this->closePayment = $closePayment;
    }

    public function process(ApiTransaction $transaction): Payment
    {
        $magentoTransaction = $this->transactionRepository->get($transaction);

        if ($magentoTransaction->getIsClosed()) {
            throw new LocalizedException(
                __('Transaction %1 is already closed', $transaction->getId())
            );
        }

        $paymentId = (int) $magentoTransaction->getPaymentId();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->paymentRepository->get($paymentId);

        /**
         * We need to update transaction id this way to avoid fetching wrong transaction during capture
         * By default Magento takes last authorization transaction
         * but our last transaction might be invalid one
         * @see \Magento\Sales\Model\Order\Payment\Operations\ProcessInvoiceOperation::execute
         *
         * @var \Magento\Sales\Model\Order\Payment $paymentReference
         */
        $paymentReference = $payment->getOrder()->getPayment();
        $paymentReference->setParentTransactionId($transaction->getSessionId());
        $payment->setParentTransactionId($transaction->getSessionId());
        $payment->setLastTransId($transaction->getSessionId());

        $payment->accept();
        $this->transactionRepository->save($transaction, $magentoTransaction);
        $payment->capture();

        $this->closePayment->execute($payment);

        return $payment;
    }
}
