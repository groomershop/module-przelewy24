<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\SessionId;

interface RefundsUuIdProviderInterface
{
    public function get(): string;
}
