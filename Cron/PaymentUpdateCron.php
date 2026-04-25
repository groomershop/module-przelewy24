<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Cron;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class PaymentUpdateCron
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    /**
     * @var \PayPro\Przelewy24\Model\PaymentUpdate
     */
    private $paymentUpdate;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \PayPro\Przelewy24\Model\PaymentUpdate $paymentUpdate,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->paymentUpdate = $paymentUpdate;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(OrderInterface::STATE, Order::STATE_PAYMENT_REVIEW)
            ->addFilter('payment_method_code', 'przelewy24%', 'like')
            ->addFilter(
                OrderInterface::CREATED_AT,
                (new \DateTime('now'))->sub(new \DateInterval('PT30M'))->format('Y-m-d H:i:s'),
                'lt'
            )->create();

        $result = $this->orderRepository->getList($searchCriteria);

        foreach ($result->getItems() as $order) {
            if (!$this->config->isPaymentAutoUpdateEnabled((int) $order->getStoreId())) {
                continue;
            }

            try {
                $this->paymentUpdate->execute((int) $order->getEntityId());
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf('Payment update cron, order #%s: ', $order->getIncrementId()) . $e->getMessage(),
                    ['exception' => $e]
                );
            }
        }
    }
}
