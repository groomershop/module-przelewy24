<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use PayPro\Przelewy24\Api\Data\ApiInfoInterface;

class ApiTransaction implements ApiInfoInterface
{
    const MERCHANT_ID = 'merchantId';
    const POS_ID = 'posId';
    const SESSION_ID = 'sessionId';
    const AMOUNT = 'amount';
    const ORIGIN_AMOUNT = 'originAmount';
    const CURRENCY = 'currency';
    const ORDER_ID = 'orderId';
    const METHOD_ID = 'methodId';
    const STATEMENT = 'statement';
    const SIGNATURE = 'sign';
    const STATUS = 'status';

    const STATUS_NO_PAYMENT = 0;
    const STATUS_ADVANCE_PAYMENT = 1;
    const STATUS_PAYMENT_MADE = 2;
    const STATUS_PAYMENT_RETURNED = 3;

    /**
     * @var int
     */
    private $merchantId;

    /**
     * @var int
     */
    private $posId;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var int
     */
    private $originAmount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var int
     */
    private $methodId;

    /**
     * @var string
     */
    private $statement;

    /**
     * @var string
     */
    private $signature;

    public function __construct(array $payload)
    {
        $this->merchantId = (int) ($payload[self::MERCHANT_ID] ?? null);
        $this->posId = (int) ($payload[self::POS_ID] ?? null);
        $this->sessionId = (string) ($payload[self::SESSION_ID] ?? null);
        $this->amount = (int) ($payload[self::AMOUNT] ?? null);
        $this->originAmount = (int) ($payload[self::ORIGIN_AMOUNT] ?? null);
        $this->currency = (string) ($payload[self::CURRENCY] ?? null);
        $this->orderId = (int) ($payload[self::ORDER_ID] ?? null);
        $this->methodId = (int) ($payload[self::METHOD_ID] ?? null);
        $this->statement = (string) ($payload[self::STATEMENT] ?? null);
        $this->signature = (string) ($payload[self::SIGNATURE] ?? null);
    }

    public function getId(): string
    {
        return $this->sessionId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getPosId(): int
    {
        return $this->posId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getOriginAmount(): int
    {
        return $this->originAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getMethodId(): int
    {
        return $this->methodId;
    }

    public function getStatement(): string
    {
        return $this->statement;
    }

    public function toArray(): array
    {
        return [
            self::MERCHANT_ID => $this->getMerchantId(),
            self::POS_ID => $this->getPosId(),
            self::SESSION_ID => $this->getSessionId(),
            self::AMOUNT => $this->getAmount(),
            self::ORIGIN_AMOUNT => $this->getOriginAmount(),
            self::CURRENCY => $this->getCurrency(),
            self::ORDER_ID => $this->getOrderId(),
            self::METHOD_ID => $this->getMethodId(),
            self::STATEMENT => $this->getStatement(),
        ];
    }

    public function isValidSignature(string $crcKey): bool
    {
        if (empty($this->signature)) {
            return false;
        }

        $signature = new ApiSignature([
            self::MERCHANT_ID => $this->getMerchantId(),
            self::POS_ID => $this->getPosId(),
            self::SESSION_ID => $this->getSessionId(),
            self::AMOUNT => $this->getAmount(),
            self::ORIGIN_AMOUNT => $this->getOriginAmount(),
            self::CURRENCY => $this->getCurrency(),
            self::ORDER_ID => $this->getOrderId(),
            self::METHOD_ID => $this->getMethodId(),
            self::STATEMENT => $this->getStatement(),
        ]);

        return $signature->sign($crcKey) === $this->signature;
    }
}
