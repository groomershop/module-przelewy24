<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Http\Client;

use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class TransactionStatusClient extends AbstractClient
{
    protected function apiCall(ApiClientInterface $apiClient, array $data): array
    {
        [ApiTransaction::SESSION_ID => $sessionId] = $data;

        $response = $apiClient->transactionStatus($sessionId);

        if (!isset($response['data']) && isset($response['error']) && $response['responseCode'] === 0) {
            // Transaction not found, instead of throwing validation error simulate no payment status
            $response['data'][ApiTransaction::SESSION_ID] = $sessionId;
            $response['data'][ApiTransaction::STATUS] = ApiTransaction::STATUS_NO_PAYMENT;
            $response['data']['paymentMethod'] = null;
            $response['data'][ApiTransaction::AMOUNT] = null;
        }

        return $response;
    }
}
