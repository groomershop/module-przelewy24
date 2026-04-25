<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

class InstalmentFilter
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GatewayConfig
     */
    private $config;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\GatewayConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @param \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface[] $paymentMethods
     * @param float $amount
     * @return array
     */
    public function execute(array $paymentMethods, float $amount): array
    {
        $instalmentMap = $this->config->getInstalmentMap();
        if (empty($instalmentMap)) {
            return $paymentMethods;
        }

        foreach ($paymentMethods as $key => $method) {
            if (!isset($instalmentMap[$method->getId()])) {
                continue;
            }

            $from = $instalmentMap[$method->getId()]['from'] ?? \INF;
            $to = $instalmentMap[$method->getId()]['to'] ?? 0;

            if ($amount < $from || $amount > $to) {
                unset($paymentMethods[$key]);
            }
        }

        return $paymentMethods;
    }
}
