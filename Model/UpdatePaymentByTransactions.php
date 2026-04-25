<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Sales\Api\Data\TransactionInterface;

class UpdatePaymentByTransactions
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->transactionRepository = $transactionRepository;
    }

    public function execute(\Magento\Sales\Model\Order\Payment $payment): void
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(TransactionInterface::CREATED_AT)
            ->setDescendingDirection()
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TransactionInterface::PAYMENT_ID, $payment->getEntityId())
            ->addFilter(TransactionInterface::IS_CLOSED, '0')
            ->addSortOrder($sortOrder)
            ->create();

        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $transactions */
        $transactions = $this->transactionRepository->getList($searchCriteria);
        $payment->setExpirationTransactionId($payment->getLastTransId());

        /** @var \Magento\Sales\Api\Data\TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            $payment->setLastTransId($transaction->getTxnId());
            $payment->update();
            if ($payment->getIsTransactionDenied() || $payment->getIsTransactionApproved()) {
                break;
            }
        }

        if (!$payment->getIsTransactionApproved()) {
            $payment->setLastTransId($payment->getExpirationTransactionId());
        }

        if ($payment->getIsTransactionApproved() || $payment->getIsTransactionDenied()) {
            foreach ($transactions as $transaction) {
                $transaction->setIsClosed(1);
                $this->transactionRepository->save($transaction);
            }
        }
    }
}
