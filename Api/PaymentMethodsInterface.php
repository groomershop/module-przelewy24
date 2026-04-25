<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

interface PaymentMethodsInterface
{
    /**
     * @param float|null $amount
     * @return \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface[]
     */
    public function execute(?float $amount = null): array;
}
