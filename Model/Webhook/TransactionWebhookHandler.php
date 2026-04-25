<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\WebhookHandlerInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class TransactionWebhookHandler implements WebhookHandlerInterface
{
    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $paymentLogger;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\InvoiceProcessor
     */
    private $invoiceProcessor;

    public function __construct(
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \PayPro\Przelewy24\Model\Processor\PaymentProcessor $paymentProcessor,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \PayPro\Przelewy24\Model\Processor\InvoiceProcessor $invoiceProcessor
    ) {
        $this->paymentLogger = $paymentLogger;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
        $this->paymentProcessor = $paymentProcessor;
        $this->orderRepository = $orderRepository;
        $this->invoiceProcessor = $invoiceProcessor;
    }

    public function handle(array $payload): void
    {
        $this->paymentLogger->debug([
            'webhook' => self::class,
            'payload' => $payload,
        ]);

        $transaction = new ApiTransaction($payload);
        if (!$transaction->isValidSignature($this->config->getCrcKey())) {
            throw new LocalizedException(
                __('Invalid signature for transaction %1', $transaction->getId())
            );
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $payment = $this->paymentProcessor->process($transaction);
            $this->orderRepository->save($payment->getOrder());
            $this->invoiceProcessor->process($payment);

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }
}
