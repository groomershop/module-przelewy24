<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use PayPro\Przelewy24\Model\Api\ApiSignature;
use PayPro\Przelewy24\Model\Api\ApiRefund;
use PHPUnit\Framework\TestCase;

class ApiRefundTest extends TestCase
{
    private const SIGNATURE_FIELDS = [
        ApiRefund::ORDER_ID,
        ApiRefund::SESSION_ID,
        ApiRefund::REFUNDS_UUID,
        ApiRefund::MERCHANT_ID,
        ApiRefund::AMOUNT,
        ApiRefund::CURRENCY,
        ApiRefund::STATUS,
    ];

    public function testIfEmptySignatureIsInvalid(): void
    {
        $model = new ApiRefund([]);
        $this->assertFalse($model->isValidSignature('crc_key'));
    }

    /**
     * @dataProvider refundDataProvider
     */
    public function testValidSignature(array $refund): void
    {
        $signaturePayload = array_filter($refund, function (string $field) {
            return in_array($field, self::SIGNATURE_FIELDS);
        }, ARRAY_FILTER_USE_KEY);

        $signature = (new ApiSignature($signaturePayload))->sign('crc_key');

        $model = new ApiRefund(array_merge($refund, [ApiRefund::SIGNATURE => $signature]));
        $this->assertTrue($model->isValidSignature('crc_key'));
    }

    /**
     * @dataProvider refundDataProvider
     */
    public function testToArray(array $refund): void
    {
        $model = new ApiRefund($refund);
        $this->assertEquals($refund, $model->toArray());
    }

    public function refundDataProvider(): array
    {
        return [
            [[
                ApiRefund::ORDER_ID     => 000000001,
                ApiRefund::SESSION_ID   => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiRefund::REQUEST_ID   => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiRefund::REFUNDS_UUID => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiRefund::MERCHANT_ID  => 1,
                ApiRefund::AMOUNT       => 6500,
                ApiRefund::CURRENCY     => 'PLN',
                ApiRefund::TIMESTAMP    => 1612349102,
                ApiRefund::STATUS       => 0,
            ]],
            [[
                ApiRefund::ORDER_ID     => 000000001,
                ApiRefund::SESSION_ID   => 'bbbbbbbb-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiRefund::REQUEST_ID   => 'bbbbbbbb-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiRefund::REFUNDS_UUID => 'bbbbbbbb-bbbb-cccc-dddd-eeeeeeeeeeee',
                ApiRefund::MERCHANT_ID  => 1,
                ApiRefund::AMOUNT       => 16500,
                ApiRefund::CURRENCY     => 'PLN',
                ApiRefund::TIMESTAMP    => 1612349102,
                ApiRefund::STATUS       => 0,
            ]],
        ];
    }
}
