<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel\CustomerAccount;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use PayPro\Przelewy24\Model\PaymentLink as PaymentLinkModel;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class PaymentLink implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Url
     */
    private $frontendUrl;

    public function __construct(
        Registry $registry,
        \Magento\Framework\Url $frontendUrl
    ) {
        $this->registry = $registry;
        $this->frontendUrl = $frontendUrl;
    }

    public function canRetryPayment(): bool
    {
        $order = $this->getOrder();
        if (!$order || !$order->getPayment()) {
            return false;
        }

        $method = (string) $order->getPayment()->getMethod();

        return strpos($method, ConfigProvider::CODE) === 0
            && !$order->isCanceled()
            && $order->getTotalDue() > 0;
    }

    public function getRetryUrl(): ?string
    {
        $order = $this->getOrder();
        if (!$order || !$order->getPayment()) {
            return null;
        }

        $lastTransId = $order->getPayment()->getLastTransId();
        if (!$lastTransId) {
            return null;
        }

        $this->frontendUrl->setScope((int) $order->getStoreId());

        return $this->frontendUrl->getUrl(PaymentLinkModel::PAYMENT_ROUTE, [
            '_nosid' => true,
            'id' => $lastTransId,
        ]);
    }

    private function getOrder(): ?\Magento\Sales\Model\Order
    {
        return $this->registry->registry('current_order');
    }
}
