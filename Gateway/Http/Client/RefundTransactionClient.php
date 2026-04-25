<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Http\Client;

use PayPro\Przelewy24\Api\ApiClientInterface;

class RefundTransactionClient extends AbstractClient
{
    protected function apiCall(ApiClientInterface $apiClient, array $data): array
    {
        return $apiClient->refundTransaction($data);
    }
}
