<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\ResourceModel\BlikNotification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPro\Przelewy24\Model\BlikNotification as Model;
use PayPro\Przelewy24\Model\ResourceModel\BlikNotification as ResourceModel;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 *
 * @method \PayPro\Przelewy24\Model\BlikNotification[] getItems()
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this->getItems() as $item) {
            $this->getResource()->unserializeFields($item);
            $item->setDataChanges(false);
        }

        return $this;
    }
}
