<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\SessionId;

interface SessionIdProviderInterface
{
    public function get(string $paymentMethod, ?array $paymentAdditional = []): string;
}
