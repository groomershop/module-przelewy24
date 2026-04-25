<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\WebhookHandlerInterface;
use PayPro\Przelewy24\Model\Api\ApiBlikNotification;

class BlikNotificationWebhookHandler implements WebhookHandlerInterface
{
    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private \Magento\Payment\Model\Method\Logger $paymentLogger;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private \PayPro\Przelewy24\Gateway\Config\CommonConfig $config;

    /**
     * @var \PayPro\Przelewy24\Model\BlikNotificationFactory
     */
    private \PayPro\Przelewy24\Model\BlikNotificationFactory $blikNotificationFactory;

    /**
     * @var \PayPro\Przelewy24\Model\ResourceModel\BlikNotification
     */
    private \PayPro\Przelewy24\Model\ResourceModel\BlikNotification $blikNotificationResource;

    public function __construct(
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \PayPro\Przelewy24\Model\BlikNotificationFactory $blikNotificationFactory,
        \PayPro\Przelewy24\Model\ResourceModel\BlikNotification $blikNotificationResource
    ) {
        $this->paymentLogger = $paymentLogger;
        $this->config = $config;
        $this->blikNotificationFactory = $blikNotificationFactory;
        $this->blikNotificationResource = $blikNotificationResource;
    }

    public function handle(array $payload): void
    {
        $this->paymentLogger->debug([
            'webhook' => self::class,
            'payload' => $payload,
        ]);

        $notification = new ApiBlikNotification($payload);
        if (!$notification->isValidSignature($this->config->getCrcKey())) {
            throw new LocalizedException(
                __('Invalid signature for Blik notification %1', $notification->getId())
            );
        }

        $blikNotification = $this->blikNotificationFactory->create();
        $blikNotification->setSessionId($notification->getSessionId());
        $blikNotification->setContent($notification->toArray());

        $this->blikNotificationResource->save($blikNotification);
    }
}
