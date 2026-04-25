<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\PaymentException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class RenewTransaction
{
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentMade
     */
    private $isPaymentMade;

    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired
     */
    private $isPaymentExpired;

    /**
     * @var \PayPro\Przelewy24\Api\PayAgainInterface
     */
    private $payAgain;

    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Api\OrderPaymentRepositoryInterface $paymentRepository,
        \PayPro\Przelewy24\Model\IsPaymentMade $isPaymentMade,
        \PayPro\Przelewy24\Model\IsPaymentExpired $isPaymentExpired,
        \PayPro\Przelewy24\Api\PayAgainInterface $payAgain
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionRepository = $transactionRepository;
        $this->paymentRepository = $paymentRepository;
        $this->isPaymentMade = $isPaymentMade;
        $this->isPaymentExpired = $isPaymentExpired;
        $this->payAgain = $payAgain;
    }

    public function execute(string $sessionId, array $additionalData = []): OrderPaymentInterface
    {
        $transactionFilter = $this->filterBuilder->setField(TransactionInterface::TXN_ID)
            ->setValue($sessionId)
            ->setConditionType('eq')
            ->create();

        $parentTransactionFilter = $this->filterBuilder->setField(TransactionInterface::PARENT_TXN_ID)
            ->setValue($sessionId)
            ->setConditionType('eq')
            ->create();

        /** @var \Magento\Framework\Api\Search\FilterGroup $transactionIdFilterGroup */
        $transactionIdFilterGroup = $this->filterGroupBuilder->addFilter($transactionFilter)
            ->addFilter($parentTransactionFilter)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups([$transactionIdFilterGroup])
            ->create();

        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $transactions */
        $transactions = $this->transactionRepository->getList($searchCriteria);
        if ($transactions->count() === 0) {
            throw new LocalizedException(__('Can\'t renew transaction, transaction not found.'));
        }

        /** @var \Magento\Sales\Api\Data\TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getTxnType() === TransactionInterface::TYPE_CAPTURE && $transaction->getIsClosed()) {
                throw new LocalizedException(__('Can\'t renew transaction, payment already made.'));
            }
        }

        /** @var \Magento\Sales\Api\Data\TransactionInterface $lastTransaction */
        $lastTransaction = $transactions->getLastItem();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->paymentRepository->get($lastTransaction->getPaymentId());

        if (strpos($payment->getMethod(), 'przelewy24') !== 0) {
            throw new LocalizedException(__('Can\'t renew transaction, different payment provider.'));
        }

        if ($this->isPaymentMade->execute($lastTransaction->getTxnId(), (int) $payment->getOrder()->getStoreId())) {
            throw new PaymentException(__('Can\'t renew transaction, payment already made.'));
        }

        if ($this->isPaymentExpired->execute($payment)) {
            throw new PaymentException(__('Can\'t renew transaction, payment expired.'));
        }

        $this->payAgain->execute($sessionId, $payment, $additionalData);

        return $payment;
    }
}
