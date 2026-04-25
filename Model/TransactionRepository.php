<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use PayPro\Przelewy24\Api\Data\ApiInfoInterface;
use PayPro\Przelewy24\Api\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionRepository = $transactionRepository;
    }

    public function get(ApiInfoInterface $info): TransactionInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TransactionInterface::TXN_ID, $info->getId())
            ->create();

        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $transactions */
        $transactions = $this->transactionRepository->getList($searchCriteria);
        if ($transactions->count() === 0) {
            throw new LocalizedException(__('Transaction %1 not found', $info->getId()));
        }

        /** @var \Magento\Sales\Api\Data\TransactionInterface $magentoTransaction */
        $magentoTransaction = $transactions->getFirstItem();

        return $magentoTransaction;
    }

    public function save(ApiInfoInterface $info, TransactionInterface $magentoTransaction): void
    {
        $magentoTransaction->setAdditionalInformation(Transaction::RAW_DETAILS, $info->toArray());
        $this->transactionRepository->save($magentoTransaction);
    }
}
