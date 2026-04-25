<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class IsPaymentMade
{
    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientFactory;

    public function __construct(
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory
    ) {
        $this->apiConfig = $apiConfig;
        $this->apiClientFactory = $apiClientFactory;
    }

    public function execute(string $sessionId, ?int $storeId = null): bool
    {
        $clientConfig = $this->apiConfig->get(ScopeInterface::SCOPE_STORE, $storeId);
        $apiClient = $this->apiClientFactory->create($clientConfig);

        $result = $apiClient->transactionStatus($sessionId);

        return !((isset($result['error']) && $result['responseCode'] === 0)
            || $result['data'][ApiTransaction::STATUS] === ApiTransaction::STATUS_NO_PAYMENT
            || $result['data'][ApiTransaction::STATUS] === ApiTransaction::STATUS_PAYMENT_RETURNED);
    }
}
