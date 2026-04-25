<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\BlikResponseInterface;

interface RegisterBlikTransactionInterface
{
    const NAME = self::class;

    /**
     * @param string $cartId
     * @param string $blikCode
     * @param bool $saveAlias
     * @return \PayPro\Przelewy24\Api\Data\BlikResponseInterface
     */
    public function execute(string $cartId, string $blikCode, bool $saveAlias = false): BlikResponseInterface;
}
