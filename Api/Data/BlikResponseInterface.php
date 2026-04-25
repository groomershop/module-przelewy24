<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface BlikResponseInterface
{
    public const SUCCESS = 'success';
    public const MESSAGE = 'message';
    public const SESSION_ID = 'sessionId';

    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @return string
     */
    public function getSessionId(): string;
}
