<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

interface ApiClientInterface
{
    const ENCODING = 'UTF-8';

    const TEST_ACCESS_ENDPOINT = '/api/v1/testAccess';
    const PAYMENT_METHODS_ENDPOINT = '/api/v1/payment/methods/%s';
    const REGISTER_TRANSACTION_ENDPOINT = '/api/v1/transaction/register';
    const VERIFY_TRANSACTION_ENDPOINT = '/api/v1/transaction/verify';
    const REFUND_TRANSACTION_ENDPOINT = '/api/v1/transaction/refund';
    const REFUND_INFO_ENDPOINT = '/api/v1/refund/by/orderId/%d';
    const TRANSACTION_STATUS_ENDPOINT = '/api/v1/transaction/by/sessionId/%s';
    const CARD_INFO_ENDPOINT = '/api/v1/card/info/%d';
    const CHARGE_CARD_ENDPOINT = '/api/v1/card/chargeWith3ds';
    const BLIK_CHARGE_BY_CODE_ENDPOINT = '/api/v1/paymentMethod/blik/chargeByCode';
    const BLIK_CHARGE_BY_ALIAS_ENDPOINT = '/api/v1/paymentMethod/blik/chargeByAlias';

    public function testAccess(): array;

    public function paymentMethods(string $lang = 'en'): array;

    public function registerTransaction(array $params): array;

    public function verifyTransaction(array $params): array;

    public function refundTransaction(array $params): array;

    public function refundInfo(int $orderId): array;

    public function transactionStatus(string $sessionId): array;

    public function cardInfo(int $orderId): array;

    public function chargeCard(string $token): array;

    public function blikChargeByCode(array $params): array;

    public function blikChargeByAlias(array $params): array;
}
