<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use PayPro\Przelewy24\Gateway\Request\TransactionStatusRequestBuilder;
use PHPUnit\Framework\TestCase;

class TransactionStatusRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $model = new TransactionStatusRequestBuilder();

        $this->assertEquals(['sessionId' => 'uuid'], $model->build(['transactionId' => 'uuid']));
    }
}
