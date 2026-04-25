<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class GooglePayAuthMethodsOptionSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $methods = ['PAN_ONLY', 'CRYPTOGRAM_3DS'];

        return array_map(function (string $method) {
            return ['value' => $method, 'label' => $method];
        }, $methods);
    }
}
