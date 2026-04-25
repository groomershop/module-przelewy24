<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Plugin;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;

class AssignERatySCBChannelDataPlugin
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Payment\Gateway\Request\BuilderInterface $subject
     * @param array $result
     * @return array
     */
    public function afterBuild(BuilderInterface $subject, array $result): array
    {
        $method = $result['method'] ?? null;
        if ($method === ApiPaymentMethodInterface::ERATY_SCB_ID) {
            $result['channel'] = 2048;
        }

        return $result;
    }
}
