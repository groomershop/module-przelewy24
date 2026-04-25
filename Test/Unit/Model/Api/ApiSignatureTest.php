<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use PayPro\Przelewy24\Model\Api\ApiSignature;
use PHPUnit\Framework\TestCase;

class ApiSignatureTest extends TestCase
{
    public function testSign(): void
    {
        $this->assertEquals(
            '5a925a64358629a80b10706f47d59ec4945b4450f9045126e28a2b3f27fb373268b6ef8b70ad4ef954071ce98ad4fada',
            (new ApiSignature([
                'sessionId' => 'str',
                'merchantId' => 'str',
                'amount' => 'int',
                'currency' => 'str',
            ]))->sign('str')
        );
    }
}
