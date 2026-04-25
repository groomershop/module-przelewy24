<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Sales\Model\Order\Payment;

class PaymentLink
{
    const LABEL = 'Pay for the order';
    const PAYMENT_ROUTE = 'przelewy24/payment/pay';
    const SUCCESS_ROUTE = 'przelewy24/payment/success';

    /**
     * @var \Magento\Framework\Url
     */
    private $frontendUrl;

    public function __construct(
        \Magento\Framework\Url $frontendUrl
    ) {
        $this->frontendUrl = $frontendUrl;
    }

    public function execute(Payment $payment): string
    {
        $this->frontendUrl->setScope((int) $payment->getOrder()->getStoreId());

        return $this->frontendUrl->getUrl(self::PAYMENT_ROUTE, [
            '_nosid' => true,
            'id' => $payment->getTransactionId(),
        ]);
    }
}
