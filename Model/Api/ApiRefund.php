<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use PayPro\Przelewy24\Api\Data\ApiInfoInterface;

class ApiRefund implements ApiInfoInterface
{
    const ORDER_ID = 'orderId';
    const SESSION_ID = 'sessionId';
    const MERCHANT_ID = 'merchantId';
    const REQUEST_ID = 'requestId';
    const REFUNDS_UUID = 'refundsUuid';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const TIMESTAMP = 'timestamp';
    const STATUS = 'status';
    const SIGNATURE = 'sign';

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $merchantId;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var string
     */
    private $refundsUuid;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $signature;

    public function __construct(array $payload)
    {
        $this->orderId = (int) ($payload[self::ORDER_ID] ?? null);
        $this->sessionId = (string) ($payload[self::SESSION_ID] ?? null);
        $this->merchantId = (int) ($payload[self::MERCHANT_ID] ?? null);
        $this->requestId = (string) ($payload[self::REQUEST_ID] ?? null);
        $this->refundsUuid = (string) ($payload[self::REFUNDS_UUID] ?? null);
        $this->amount = (int) ($payload[self::AMOUNT] ?? null);
        $this->currency = (string) ($payload[self::CURRENCY] ?? null);
        $this->timestamp = (int) ($payload[self::TIMESTAMP] ?? null);
        $this->status = (int) ($payload[self::STATUS] ?? null);
        $this->signature = (string) ($payload[self::SIGNATURE] ?? null);
    }

    public function getId(): string
    {
        return $this->refundsUuid;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getRefundsUuid(): string
    {
        return $this->refundsUuid;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            self::ORDER_ID => $this->getOrderId(),
            self::SESSION_ID => $this->getSessionId(),
            self::MERCHANT_ID => $this->getMerchantId(),
            self::REQUEST_ID => $this->getRequestId(),
            self::REFUNDS_UUID => $this->getRefundsUuid(),
            self::AMOUNT => $this->getAmount(),
            self::CURRENCY => $this->getCurrency(),
            self::TIMESTAMP => $this->getTimestamp(),
            self::STATUS => $this->getStatus(),
        ];
    }

    public function isValidSignature(string $crcKey): bool
    {
        if (empty($this->signature)) {
            return false;
        }

        $signature = new ApiSignature([
            self::ORDER_ID => $this->getOrderId(),
            self::SESSION_ID => $this->getSessionId(),
            self::REFUNDS_UUID => $this->getRefundsUuid(),
            self::MERCHANT_ID => $this->getMerchantId(),
            self::AMOUNT => $this->getAmount(),
            self::CURRENCY => $this->getCurrency(),
            self::STATUS => $this->getStatus(),
        ]);

        return $signature->sign($crcKey) === $this->signature;
    }
}
