<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface BlikNotificationInterface
{
    public const NOTIFICATION_ID = 'notification_id';
    public const SESSION_ID = 'session_id';
    public const ORDER_ID = 'orderId';
    public const METHOD = 'method';
    public const RESULT = 'result';
    public const ERROR = 'error';
    public const MESSAGE = 'message';
    public const STATUS = 'status';
    public const CONTENT = 'content';
    public const CREATED_AT = 'created_at';

    /**
     * @return string
     */
    public function getSessionId(): string;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @return int
     */
    public function getMethod(): int;

    /**
     * @return string
     */
    public function getError(): string;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return bool
     */
    public function isSuccess(): bool;
}
