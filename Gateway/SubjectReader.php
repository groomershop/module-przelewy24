<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class SubjectReader
{
    private const ERROR = 'error';
    private const DATA = 'data';
    private const TOKEN = 'token';
    private const MESSAGE = 'message';

    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($subject);
    }

    public function readAmount(array $subject): float
    {
        return (float) \Magento\Payment\Gateway\Helper\SubjectReader::readAmount($subject);
    }

    public function readCurrency(array $subject): ?string
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->readPayment($subject)->getPayment();
        return $payment->getOrder()->getOrderCurrencyCode();
    }

    public function readOrderAmount(array $subject): float
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->readPayment($subject)->getPayment();
        return (float) $payment->getOrder()->getGrandTotal();
    }

    public function readOrderCurrencyAmount(array $subject): float
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->readPayment($subject)->getPayment();
        $order = $payment->getOrder();
        $rate = (float) $order->getBaseToOrderRate();
        $baseAmount = $this->readAmount($subject);
        return $rate > 0.0 ? $baseAmount * $rate : $baseAmount;
    }

    public function readOrderStoreId(array $subject): int
    {
        $payment = $this->readPayment($subject);
        return (int) $payment->getOrder()->getStoreId();
    }

    public function readOrderIncrementId(array $subject): string
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->readPayment($subject)->getPayment();

        return (string) $payment->getOrder()->getIncrementId();
    }

    public function readTransactionId(array $subject): string
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->readPayment($subject)->getPayment();

        /** @var string|null $transactionId */
        $transactionId = $payment->getTransactionId();

        return (string) (
            $transactionId ?? $payment->getAdditionalInformation()['sessionId'] ?? 'unknown'
        );
    }

    public function readResponse(array $subject): array
    {
        $response = \Magento\Payment\Gateway\Helper\SubjectReader::readResponse($subject);
        $data = $response[self::DATA] ?? [];

        return is_array($data) ? $data : [];
    }

    public function readResponseError(array $subject): ?string
    {
        $response = \Magento\Payment\Gateway\Helper\SubjectReader::readResponse($subject);
        if (!isset($response[self::ERROR])) {
            return null;
        }

        return is_string($response[self::ERROR])
            ? $response[self::ERROR]
            : (string) ($response[self::ERROR][self::MESSAGE] ?? null);
    }

    public function readTransactionToken(array $response): ?string
    {
        return $response[self::DATA][self::TOKEN] ?? null;
    }
}
