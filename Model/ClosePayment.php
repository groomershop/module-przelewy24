<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class ClosePayment
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionRepository = $transactionRepository;
        $this->logger = $logger;
    }

    public function execute(OrderPaymentInterface $payment): void
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TransactionInterface::PAYMENT_ID, $payment->getEntityId())
            ->addFilter(TransactionInterface::IS_CLOSED, '0')
            ->create();

        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $transactions */
        $transactions = $this->transactionRepository->getList($searchCriteria);

        /** @var \Magento\Sales\Api\Data\TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            try {
                $transaction->setIsClosed(1);
                $this->transactionRepository->save($transaction);
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf('Can\'t save transaction %s as closed during payment close', $transaction->getTxnId())
                );
            }
        }
    }
}
