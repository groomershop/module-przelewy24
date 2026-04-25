<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use PayPro\Przelewy24\Model\Api\ApiAmount;
use PHPUnit\Framework\TestCase;

class ApiAmountTest extends TestCase
{
    /**
     * @dataProvider amountDataProvider
     */
    public function testFormat(?float $input, int $output): void
    {
        $this->assertEquals($output, (new ApiAmount($input))->format());
    }

    public function amountDataProvider(): array
    {
        return [
            [0.01, 1],
            [100.00, 10000],
            [null, 0],
            [1.23, 123],
        ];
    }
}
