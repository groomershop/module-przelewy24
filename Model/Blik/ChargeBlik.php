<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use Magento\Payment\Gateway\Http\ClientException;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Model\Api\ApiConfig;

class ChargeBlik
{
    public const TOKEN = 'token';
    public const BLIK_CODE = 'blikCode';

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private \Magento\Payment\Model\Method\Logger $paymentLogger;

    public function __construct(
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Payment\Model\Method\Logger $paymentLogger
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->apiConfig = $apiConfig;
        $this->logger = $logger;
        $this->paymentLogger = $paymentLogger;
    }

    public function execute(array $data, int $storeId): array
    {
        $clientConfig = $this->apiConfig->get(ScopeInterface::SCOPE_STORE, $storeId);
        $apiClient = $this->apiClientFactory->create($clientConfig);

        try {
            $response = $apiClient->blikChargeByCode($data);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            throw new ClientException(__($e->getMessage()));
        } finally {
            $this->paymentLogger->debug([
                'url' => $clientConfig[ApiConfig::URL],
                'username' => $clientConfig[ApiConfig::USERNAME],
                'request' => $data,
                'client' => static::class,
                'response' => $response ?? null,
            ]);
        }

        return $response;
    }
}
