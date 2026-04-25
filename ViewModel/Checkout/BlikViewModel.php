<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel\Checkout;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use PayPro\Przelewy24\Api\Data\BlikNotificationInterface;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class BlikViewModel implements ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private \Magento\Checkout\Model\Session $checkoutSession;

    /**
     * @var \PayPro\Przelewy24\Api\GetBlikNotificationInterface
     */
    private \PayPro\Przelewy24\Api\GetBlikNotificationInterface $getBlikNotification;

    /**
     * @var \PayPro\Przelewy24\Model\Ui\ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var (\PayPro\Przelewy24\Api\Data\BlikNotificationInterface|null)[]
     */
    private array $blikNotifications = [];

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \PayPro\Przelewy24\Api\GetBlikNotificationInterface $getBlikNotification,
        ConfigProvider $configProvider
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->getBlikNotification = $getBlikNotification;
        $this->configProvider = $configProvider;
    }

    public function isBlikPayment(): bool
    {
        return $this->getPayment() && $this->getPayment()->getMethod() === ConfigProvider::BLIK_CODE;
    }

    public function hasSuccessNotification(): bool
    {
        $notification = $this->getNotification();

        return $notification !== null && $notification->isSuccess();
    }

    public function getSessionId(): string
    {
        if (!$this->getPayment() instanceof \Magento\Sales\Model\Order\Payment) {
            return '';
        }
        $transaction = $this->getPayment()->getAuthorizationTransaction();

        return $transaction ? $transaction->getTxnId() : '';
    }

    public function getCartId(): string
    {
        return (string) $this->getOrder()->getQuoteId();
    }

    public function getBlikConfig(): array
    {
        return $this->configProvider->getConfig()[ConfigProvider::PAYMENT][ConfigProvider::BLIK_CODE] ?? [];
    }

    private function getPayment(): ?\Magento\Sales\Api\Data\OrderPaymentInterface
    {
        return $this->getOrder()->getPayment();
    }

    private function getOrder(): \Magento\Sales\Model\Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function getNotification(): ?BlikNotificationInterface
    {
        $sessionId = $this->getSessionId();
        if (!$sessionId) {
            return null;
        }

        if (!isset($this->blikNotifications[$sessionId])) {
            $this->blikNotifications[$sessionId] = $this->getBlikNotification->execute($sessionId);
        }

        return $this->blikNotifications[$sessionId];
    }
}
