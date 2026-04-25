<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Plugin;

use PayPro\Przelewy24\Gateway\Config\BlikConfig;
use PayPro\Przelewy24\Gateway\Config\GatewayConfig;

class RemoveBlikStandaloneWhenLvl0ActivePlugin
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\BlikConfig
     */
    private $blikConfig;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\BlikConfig $blikConfig
    ) {
        $this->blikConfig = $blikConfig;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \PayPro\Przelewy24\Gateway\Config\GatewayConfig $subject
     * @param array $result
     * @param int|null $storeId
     * @return array
     */
    public function afterGetStandaloneMethods(GatewayConfig $subject, array $result, ?int $storeId = null): array
    {
        if (!$this->isInStoreBlikEnabled($storeId)) {
            return $result;
        }

        return $this->withoutBlikInStore($result);
    }

    private function isInStoreBlikEnabled(?int $storeId): bool
    {
        return $this->blikConfig->isActive($storeId);
    }

    private function withoutBlikInStore(array $result): array
    {
        return array_filter($result, function ($paymentMethod) {
            return (int) $paymentMethod['id'] !== BlikConfig::BLIK_IN_STORE_ID;
        });
    }
}
