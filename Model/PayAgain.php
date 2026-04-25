<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\PayAgainInterface;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PayPro\Przelewy24\Observer\GatewayDataAssignObserver;

class PayAgain implements PayAgainInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function execute(string $sessionId, Payment $payment, array $additionalData): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        $order = $payment->getOrder();

        try {
            $payment->unsAdditionalInformation(GatewayDataAssignObserver::METHOD);
            $payment->setMethod(ConfigProvider::CODE);
            $payment->setData(TransactionPayloadInterface::PAYMENT_RETURN_ROUTE, PaymentLink::SUCCESS_ROUTE);
            $payment->setParentTransactionId($sessionId);
            $payment->place();
            $order->setPayment($payment);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error(
                sprintf('Can\'t process new payment for order %s', $order->getIncrementId()),
                ['exception' => $e]
            );

            throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t process new payment'), $e);
        }
    }
}
