<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

class BlikResponse implements \PayPro\Przelewy24\Api\Data\BlikResponseInterface
{
    /**
     * @var bool
     */
    private bool $success;

    /**
     * @var string
     */
    private string $message;

    /**
     * @var string
     */
    private string $sessionId;

    public function __construct(bool $success, string $message, string $sessionId)
    {
        $this->success = $success;
        $this->message = $message;
        $this->sessionId = $sessionId;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
