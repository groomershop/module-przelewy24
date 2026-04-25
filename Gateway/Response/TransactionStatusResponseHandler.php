<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class TransactionStatusResponseHandler implements HandlerInterface
{
    private const DATA = 'data';
    private const PAYMENT_METHOD = 'paymentMethod';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var \PayPro\Przelewy24\Model\DenyExpiredPayment
     */
    private $denyExpiredPayment;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \PayPro\Przelewy24\Model\Processor\PaymentProcessor $paymentProcessor,
        \PayPro\Przelewy24\Model\DenyExpiredPayment $denyExpiredPayment,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->paymentProcessor = $paymentProcessor;
        $this->denyExpiredPayment = $denyExpiredPayment;
        $this->logger = $logger;
    }

    public function handle(array $handlingSubject, array $response): void
    {
        [self::DATA => $transactionPayload] = $response;

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $transaction = new ApiTransaction(
            $this->processTransactionPayload($transactionPayload, $paymentDO->getOrder())
        );

        if ($transactionPayload[ApiTransaction::STATUS] === ApiTransaction::STATUS_NO_PAYMENT
            || $transactionPayload[ApiTransaction::STATUS] === ApiTransaction::STATUS_PAYMENT_RETURNED) {
            $this->denyExpiredPayment->execute($paymentDO, $transaction);

            return;
        }

        try {
            $this->paymentProcessor->process($transaction);
        } catch (LocalizedException $e) {
            $this->logger->error('Przelewy24: payment processing failed', [
                'response' => $response,
                'exception' => $e,
            ]);
            throw new CommandException(__('Payment update failed. Please try again later.'), $e);
        }

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        $payment->setIsTransactionApproved(true);
    }

    private function processTransactionPayload(array $payload, OrderAdapterInterface $orderAdapter): array
    {
        $storeId = (int) $orderAdapter->getStoreId();

        $payload[ApiTransaction::METHOD_ID] = $payload[self::PAYMENT_METHOD];
        $payload[ApiTransaction::ORIGIN_AMOUNT] = $payload[ApiTransaction::AMOUNT];
        $payload[ApiTransaction::MERCHANT_ID] = $this->config->getMerchantId($storeId);
        $payload[ApiTransaction::POS_ID] = $this->config->getPosId($storeId);

        return $payload;
    }
}
