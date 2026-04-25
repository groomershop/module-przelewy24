<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\WebhookHandlerInterface;
use PayPro\Przelewy24\Model\Api\ApiRefund;

class RefundWebhookHandler implements WebhookHandlerInterface
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
     * @var \PayPro\Przelewy24\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \PayPro\Przelewy24\Api\TransactionRepositoryInterface $transactionRepository
    ) {
        $this->paymentLogger = $paymentLogger;
        $this->config = $config;
        $this->transactionRepository = $transactionRepository;
    }

    public function handle(array $payload): void
    {
        $this->paymentLogger->debug([
            'webhook' => self::class,
            'payload' => $payload,
        ]);

        $refund = new ApiRefund($payload);
        if (!$refund->isValidSignature($this->config->getCrcKey())) {
            throw new LocalizedException(
                __('Invalid signature for refund %1', $refund->getId())
            );
        }

        $magentoTransaction = $this->transactionRepository->get($refund);
        if ($magentoTransaction->getIsClosed()) {
            throw new LocalizedException(
                __('Refund %1 is already closed', $refund->getId())
            );
        }

        $magentoTransaction->setIsClosed(1);
        $this->transactionRepository->save($refund, $magentoTransaction);
    }
}
