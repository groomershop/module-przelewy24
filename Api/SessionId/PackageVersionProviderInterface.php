<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\SessionId;

interface PackageVersionProviderInterface
{
    public function get(): string;
}
