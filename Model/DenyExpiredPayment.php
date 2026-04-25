<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class DenyExpiredPayment
{
    /**
     * @var \PayPro\Przelewy24\Model\IsPaymentExpired
     */
    private $isPaymentExpired;

    /**
     * @var \PayPro\Przelewy24\Model\DenyPayment
     */
    private $denyPayment;

    public function __construct(
        \PayPro\Przelewy24\Model\IsPaymentExpired $isPaymentExpired,
        \PayPro\Przelewy24\Model\DenyPayment $denyPayment
    ) {
        $this->isPaymentExpired = $isPaymentExpired;
        $this->denyPayment = $denyPayment;
    }

    public function execute(PaymentDataObjectInterface $paymentDataObject, ApiTransaction $transaction): void
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDataObject->getPayment();

        if ($this->isPaymentExpired->execute($payment)) {
            $this->denyPayment->execute($paymentDataObject, $transaction);
        }
    }
}
