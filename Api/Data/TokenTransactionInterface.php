<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface TokenTransactionInterface
{
    const TOKEN = 'token';
    const TRANSACTION = 'transaction';

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @return string
     */
    public function getTransaction(): string;
}
