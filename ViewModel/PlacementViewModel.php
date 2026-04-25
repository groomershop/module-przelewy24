<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class PlacementViewModel implements ArgumentInterface
{
    public const PRZELEWY24_PLACEMENT_IMAGE_URL = 'https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/magento/banner/p24_magento_banner_800x250.png'; // @phpcs:ignore
    public const PRZELEWY24_PLACEMENT_LINK_URL = 'https://www.przelewy24.pl/magento-placement-adv/';

    /**
     * @var \Magento\Framework\Registry
     */
    private \Magento\Framework\Registry $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function getPlacementImageUrl(): string
    {
        return self::PRZELEWY24_PLACEMENT_IMAGE_URL;
    }

    public function getPlacementLinkUrl(): string
    {
        return self::PRZELEWY24_PLACEMENT_LINK_URL;
    }

    public function isPlacementVisibleInOrderView(): bool
    {
        $payment = $this->getOrder()->getPayment();
        if (!$payment) {
            return false;
        }

        $paymentMethod = (string) $payment->getMethod();

        return strpos($paymentMethod, ConfigProvider::CODE) === 0;
    }

    private function getOrder(): Order
    {
        return $this->registry->registry('sales_order');
    }
}
