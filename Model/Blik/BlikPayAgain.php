<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Api\Data\BlikResponseInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Model\RegisterTransaction;
use PayPro\Przelewy24\Observer\TokenDataAssignObserver;

class BlikPayAgain implements \PayPro\Przelewy24\Api\PayAgainInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder
     */
    private \PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder $blikPSURequestBuilder;

    /**
     * @var \PayPro\Przelewy24\Model\RegisterTransaction
     */
    private \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction;

    /**
     * @var \PayPro\Przelewy24\Model\Blik\ChargeBlik
     */
    private \PayPro\Przelewy24\Model\Blik\ChargeBlik $chargeBlik;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private \Magento\Framework\App\ResourceConnection $resourceConnection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private \Magento\Sales\Api\OrderRepositoryInterface $orderRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder $blikPSURequestBuilder,
        \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction,
        \PayPro\Przelewy24\Model\Blik\ChargeBlik $chargeBlik,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->blikPSURequestBuilder = $blikPSURequestBuilder;
        $this->registerTransaction = $registerTransaction;
        $this->chargeBlik = $chargeBlik;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function execute(string $sessionId, Payment $payment, array $additionalData): void
    {
        if (!isset($additionalData[ChargeBlik::BLIK_CODE])) {
            throw new LocalizedException(__('Blik code is missing.'));
        }

        $order = $payment->getOrder();
        $additionalPayload = $this->blikPSURequestBuilder->build([]);
        $additionalPayload[TransactionPayloadInterface::URL_CARD_PAYMENT_NOTIFICATION]
            = $order->getStore()->getUrl(TransactionPayloadInterface::BLIK_NOTIFICATION_ROUTE);

        [
            RegisterTransaction::RESPONSE => $response,
            RegisterTransaction::PAYLOAD => $payload,
        ] = $this->registerTransaction->executeForOrder($order, $additionalPayload);

        $chargeResponse = $this->chargeBlik->execute([
            ChargeBlik::TOKEN => $response['token'],
            ChargeBlik::BLIK_CODE => $additionalData[ChargeBlik::BLIK_CODE],
        ], (int) $order->getStoreId());

        $payment->setBlikResponse([
            BlikResponseInterface::SUCCESS => !isset($chargeResponse['error']),
            BlikResponseInterface::MESSAGE => isset($chargeResponse['error'])
                ? (string) __('Transaction has been declined. Please try again later.')
                : (string) __('Confirm payment in banking application.'),
            BlikResponseInterface::SESSION_ID => $payload[TransactionPayloadInterface::SESSION_ID],
        ]);

        if (!isset($chargeResponse['error'])) {
            $this->processPayment($payment, $sessionId, $payload[TransactionPayloadInterface::SESSION_ID]);
        }
    }

    private function processPayment(Payment $payment, string $sessionId, string $newSessionId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        $order = $payment->getOrder();

        try {
            $payment->setParentTransactionId($sessionId);
            $payment->setAdditionalInformation(TokenDataAssignObserver::SESSION_ID, $newSessionId);
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

            throw new LocalizedException(__('Can\'t process new payment'), $e);
        }
    }
}
