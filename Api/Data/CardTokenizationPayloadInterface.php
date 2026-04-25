<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface CardTokenizationPayloadInterface
{
    const MERCHANT_ID = 'merchantId';
    const SESSION_ID = 'sessionId';
    const SIGNATURE = 'signature';

    /**
     * @return int
     */
    public function getMerchantId(): int;

    /**
     * @return string
     */
    public function getSessionId(): string;

    /**
     * @return string
     */
    public function getSignature(): string;
}
