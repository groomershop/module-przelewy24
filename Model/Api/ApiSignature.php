<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

class ApiSignature
{
    const CRC = 'crc';

    /**
     * @var string[]
     */
    private $parameters;

    public function __construct(
        array $parameters
    ) {
        $this->parameters = $parameters;
    }

    public function sign(string $crcKey): string
    {
        return hash('sha384', (string) json_encode(
            array_merge($this->parameters, [self::CRC => $crcKey]),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
    }
}
