<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface ApiInfoInterface
{
    public function getId(): string;

    public function toArray(): array;

    public function isValidSignature(string $crcKey): bool;
}
