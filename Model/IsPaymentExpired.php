<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Sales\Model\Order\Payment;

class IsPaymentExpired
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository
     */
    private $transactionRepository;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository
    ) {
        $this->config = $config;
        $this->transactionRepository = $transactionRepository;
    }

    public function execute(Payment $payment): bool
    {
        $timeLimit = $this->config->getPaymentTimeLimit(
            (int) $payment->getAdditionalInformation('method'),
            (int) $payment->getOrder()->getStoreId()
        );
        if ($timeLimit === 0) {
            return false;
        }

        try {
            /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
            $transaction = $this->transactionRepository->getByTransactionId(
                $payment->getExpirationTransactionId() ?? $payment->getLastTransId(),
                (int) $payment->getEntityId(),
                (int) $payment->getOrder()->getEntityId()
            );
            $paymentDeadline = (new \DateTime((string) $transaction->getCreatedAt()))
                ->add(new \DateInterval('PT' . $timeLimit . 'M'));
            $now = new \DateTime();
        } catch (\Exception $e) {
            return false;
        }

        return $now > $paymentDeadline;
    }
}
