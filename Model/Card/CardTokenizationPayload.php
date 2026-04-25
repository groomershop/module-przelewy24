<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Card;

use PayPro\Przelewy24\Api\Data\CardTokenizationPayloadInterface;

class CardTokenizationPayload implements CardTokenizationPayloadInterface
{
    /**
     * @var int
     */
    private $merchantId;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $signature;

    public function __construct(int $merchantId, string $sessionId, string $signature)
    {
        $this->merchantId = $merchantId;
        $this->sessionId = $sessionId;
        $this->signature = $signature;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }
}
