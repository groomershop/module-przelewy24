<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use PayPro\Przelewy24\Api\Data\BlikNotificationInterface;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class BlikNotification extends AbstractDb
{
    public const TABLE = 'przelewy24_blik_notification';

    /**
     * @var array[]
     */
    protected $_serializableFields = [BlikNotificationInterface::CONTENT => [[], []]];

    protected function _construct()
    {
        $this->_init(self::TABLE, BlikNotificationInterface::NOTIFICATION_ID);
    }
}
