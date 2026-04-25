<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

class TransactionUrl
{
    const KEY = 'transaction_url';

    private const ROUTE = '/trnRequest/';

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config
    ) {
        $this->config = $config;
    }

    public function get(string $token, ?int $storeId = null): string
    {
        return rtrim($this->config->getGatewayUrl($storeId), '/') . self::ROUTE . $token;
    }
}
