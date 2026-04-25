<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use PayPro\Przelewy24\Model\Api\ApiSignature;
use PayPro\Przelewy24\Model\Api\ApiTransaction;
use PHPUnit\Framework\TestCase;

class ApiTransactionTest extends TestCase
{
    public function testIfEmptySignatureIsInvalid(): void
    {
        $model = new ApiTransaction([]);
        $this->assertFalse($model->isValidSignature('crc_key'));
    }

    /**
     * @dataProvider transactionDataProvider
     */
    public function testValidSignature(array $transaction): void
    {
        $signature = (new ApiSignature($transaction))->sign('crc_key');
        $model = new ApiTransaction(array_merge($transaction, [ApiTransaction::SIGNATURE => $signature]));
        $this->assertTrue($model->isValidSignature('crc_key'));
    }

    /**
     * @dataProvider transactionDataProvider
     */
    public function testToArray(array $transaction): void
    {
        $model = new ApiTransaction($transaction);
        $this->assertEquals($transaction, $model->toArray());
    }

    public function transactionDataProvider(): array
    {
        return [
            [[
                ApiTransaction::MERCHANT_ID   => 1,
                ApiTransaction::POS_ID        => 1,
                ApiTransaction::SESSION_ID    => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiTransaction::AMOUNT        => 6500,
                ApiTransaction::ORIGIN_AMOUNT => 6500,
                ApiTransaction::CURRENCY      => 'PLN',
                ApiTransaction::ORDER_ID      => 000000001,
                ApiTransaction::METHOD_ID     => 181,
                ApiTransaction::STATEMENT     => 'p24-000-000-001',
            ]],
            [[
                ApiTransaction::MERCHANT_ID   => 1,
                ApiTransaction::POS_ID        => 1,
                ApiTransaction::SESSION_ID    => 'bbbbbbbb-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiTransaction::AMOUNT        => 16500,
                ApiTransaction::ORIGIN_AMOUNT => 16500,
                ApiTransaction::CURRENCY      => 'PLN',
                ApiTransaction::ORDER_ID      => 000000002,
                ApiTransaction::METHOD_ID     => 999,
                ApiTransaction::STATEMENT     => 'p24-000-000-002',
            ]],
            [[
                ApiTransaction::MERCHANT_ID   => 1,
                ApiTransaction::POS_ID        => 1,
                ApiTransaction::SESSION_ID    => 'cccccccc-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiTransaction::AMOUNT        => 4511,
                ApiTransaction::ORIGIN_AMOUNT => 4511,
                ApiTransaction::CURRENCY      => 'PLN',
                ApiTransaction::ORDER_ID      => 000000003,
                ApiTransaction::METHOD_ID     => 1,
                ApiTransaction::STATEMENT     => 'p24-000-000-001',
            ]],
            [[
                ApiTransaction::MERCHANT_ID   => 1,
                ApiTransaction::POS_ID        => 1,
                ApiTransaction::SESSION_ID    => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiTransaction::AMOUNT        => 1501,
                ApiTransaction::ORIGIN_AMOUNT => 1501,
                ApiTransaction::CURRENCY      => 'PLN',
                ApiTransaction::ORDER_ID      => 000000004,
                ApiTransaction::METHOD_ID     => 245,
                ApiTransaction::STATEMENT     => 'p24-000-000-001',
            ]],
        ];
    }
}
