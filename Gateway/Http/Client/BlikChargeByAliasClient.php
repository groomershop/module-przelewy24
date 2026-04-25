<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;

class BlikChargeByAliasClient extends AbstractClient
{
    protected function apiCall(ApiClientInterface $apiClient, array $data): array
    {
        $transactionResult = $apiClient->registerTransaction($data);

        $token = $transactionResult['data']['token'] ?? null;
        if ($token === null) {
            throw new ClientException(__('Transaction token not found'));
        }

        $result = $apiClient->blikChargeByAlias([
            'token' => $token,
            'type' => 'alias',
        ]);

        $result['sessionId'] = $data[TransactionPayloadInterface::SESSION_ID];

        return $result;
    }
}
