<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;

interface RegisterCardVaultTransactionInterface
{
    const NAME = self::class;

    /**
     * @param int $customerId
     * @param string $cartId
     * @param string $hash
     * @return \PayPro\Przelewy24\Api\Data\TokenTransactionInterface
     */
    public function execute(int $customerId, string $cartId, string $hash): TokenTransactionInterface;
}
