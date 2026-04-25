<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use PayPro\Przelewy24\Api\Data\BlikNotificationInterface;
use PayPro\Przelewy24\Api\GetBlikNotificationInterface;
use PayPro\Przelewy24\Model\ResourceModel\BlikNotification\Collection;

class GetBlikNotification implements GetBlikNotificationInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\ResourceModel\BlikNotification\CollectionFactory
     */
    private \PayPro\Przelewy24\Model\ResourceModel\BlikNotification\CollectionFactory $collectionFactory;

    public function __construct(
        \PayPro\Przelewy24\Model\ResourceModel\BlikNotification\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(string $sessionId): ?BlikNotificationInterface
    {
        $notificationCollection = $this->collectionFactory->create();
        $notificationCollection->addFieldToFilter(BlikNotificationInterface::SESSION_ID, $sessionId);
        $notificationCollection->setOrder(BlikNotificationInterface::CREATED_AT, Collection::SORT_ORDER_DESC);
        /** @var \PayPro\Przelewy24\Model\BlikNotification $notification */
        $notification = $notificationCollection->getFirstItem();
        if (!$notification->getNotificationId()) {
            return null;
        }

        return $notification;
    }
}
