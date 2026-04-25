<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;

interface RegisterApplePayTransactionInterface
{
    const NAME = self::class;

    /**
     * @param string $cartId
     * @param string $tokenObject
     * @param string|null $returnUrl
     * @return \PayPro\Przelewy24\Api\Data\TokenTransactionInterface
     */
    public function execute(string $cartId, string $tokenObject, ?string $returnUrl = null): TokenTransactionInterface;
}
