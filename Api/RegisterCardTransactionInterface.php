<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;

interface RegisterCardTransactionInterface
{
    const NAME = self::class;

    /**
     * @param string $cartId
     * @param string $sessionId
     * @param string $token
     * @return \PayPro\Przelewy24\Api\Data\TokenTransactionInterface
     */
    public function execute(string $cartId, string $sessionId, string $token): TokenTransactionInterface;
}
