<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use PayPro\Przelewy24\Model\TokenTransaction;
use PHPUnit\Framework\TestCase;

class TokenTransactionTest extends TestCase
{
    public function testModel(): void
    {
        $token = 'TOKEN';
        $transaction = 'uuid';

        $tokenTransaction = new TokenTransaction($token, $transaction);
        $this->assertEquals($token, $tokenTransaction->getToken());
        $this->assertEquals($transaction, $tokenTransaction->getTransaction());
    }
}
