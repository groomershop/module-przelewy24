<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

interface PaymentMethodsModifierInterface
{
    /**
     * @param \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface[] $paymentMethods
     * @return \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modify(array $paymentMethods): array;
}
