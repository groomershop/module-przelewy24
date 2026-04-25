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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function execute(Payment $payment): string
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($payment->getOrder()->getStoreId());

        return $store->getUrl(self::PAYMENT_ROUTE, ['id' => $payment->getTransactionId()]);
    }
}
