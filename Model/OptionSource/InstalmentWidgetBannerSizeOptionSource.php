<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class InstalmentWidgetBannerSizeOptionSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'mini', 'label' => __('MINI')],
            ['value' => 'max', 'label' => __('MAX')],
        ];
    }
}
