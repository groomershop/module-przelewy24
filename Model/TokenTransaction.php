<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;

class TokenTransaction implements TokenTransactionInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $transaction;

    public function __construct(string $token, string $transaction)
    {
        $this->token = $token;
        $this->transaction = $transaction;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getTransaction(): string
    {
        return $this->transaction;
    }
}
