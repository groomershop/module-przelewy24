<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Config;

interface CurrencyConfigAwareInterface
{
    const ALLOWED_CURRENCIES = 'allowed_currencies';

    public function isCurrencyAllowed(string $currencyCode, ?int $storeId = null): bool;
}
