<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class GooglePayCardNetworksOptionSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $networks = ['AMEX', 'DISCOVER', 'INTERAC', 'JCB', 'MASTERCARD', 'VISA'];

        return array_map(function (string $network) {
            return ['value' => $network, 'label' => $network];
        }, $networks);
    }
}
