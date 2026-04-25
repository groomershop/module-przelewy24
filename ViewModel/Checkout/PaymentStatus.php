<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel\Checkout;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class PaymentStatus implements ArgumentInterface
{
    const PAID = 2;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientInterfaceFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientInterfaceFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->apiConfig = $apiConfig;
        $this->apiClientInterfaceFactory = $apiClientInterfaceFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function isVisible(): bool
    {
        $order = $this->checkoutSession->getLastRealOrder();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        return $payment->getMethod() === ConfigProvider::CODE;
    }

    public function isTransactionPaid(): bool
    {
        $order = $this->checkoutSession->getLastRealOrder();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        $transaction = $payment->getAuthorizationTransaction();
        if (!$transaction) {
            return false;
        }

        try {
            /** @var \PayPro\Przelewy24\Api\ApiClientInterface $apiClient */
            $apiClient = $this->apiClientInterfaceFactory->create($this->apiConfig->get());
            $response = $apiClient->transactionStatus($transaction->getTxnId());
            ['data' => $transactionInfo] = $response;

            $status = $transactionInfo['status'] ?? null;

            return $status === self::PAID;
        } catch (\Exception $e) {
            return false;
        }
    }
}
