<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use PayPro\Przelewy24\Api\Data\ApiInfoInterface;

class ApiBlikNotification implements ApiInfoInterface
{
    public const ORDER_ID = 'orderId';
    public const SESSION_ID = 'sessionId';
    public const METHOD = 'method';
    public const RESULT = 'result';
    public const ERROR = 'error';
    public const MESSAGE = 'message';
    public const STATUS = 'status';
    public const TRX_REF = 'trxRef';
    public const SIGNATURE = 'sign';

    /** @var int  */
    private int $orderId;
    /** @var string  */
    private string $sessionId;
    /** @var int  */
    private int $method;
    /** @var string[] */
    private array $result;
    /** @var string */
    private string $signature;

    public function __construct(array $payload)
    {
        $this->orderId = (int) ($payload[self::ORDER_ID] ?? null);
        $this->sessionId = (string) ($payload[self::SESSION_ID] ?? null);
        $this->method = (int) ($payload[self::METHOD] ?? null);
        $this->result = (array) ($payload[self::RESULT] ?? []);
        $this->signature = (string) ($payload[self::SIGNATURE] ?? null);
    }

    public function getId(): string
    {
        return $this->sessionId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getMethod(): int
    {
        return $this->method;
    }

    /**
     * @return string[]
     */
    public function getResult(): array
    {
        return $this->result;
    }

    public function getError(): string
    {
        return $this->result[self::ERROR] ?? '';
    }

    public function getMessage(): string
    {
        return $this->result[self::MESSAGE] ?? '';
    }

    public function getStatus(): string
    {
        return $this->result[self::STATUS] ?? '';
    }

    public function getTrxRef(): string
    {
        return $this->result[self::TRX_REF] ?? '';
    }

    public function toArray(): array
    {
        return [
            self::ORDER_ID => $this->getOrderId(),
            self::SESSION_ID => $this->getSessionId(),
            self::METHOD => $this->getMethod(),
            self::RESULT => $this->getResult(),
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
            self::METHOD => $this->getMethod(),
            self::RESULT => $this->getResult(),
        ]);

        return $signature->sign($crcKey) === $this->signature;
    }
}
