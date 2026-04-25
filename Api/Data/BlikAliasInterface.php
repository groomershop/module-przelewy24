<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface BlikAliasInterface
{
    const VAULT_TOKEN_TYPE = 'blik';

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string
     */
    public function getValue(): string;
}
