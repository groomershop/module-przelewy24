<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use PayPro\Przelewy24\Api\ApiClientInterface;

abstract class AbstractClient implements ClientInterface
{
    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $paymentLogger;

    public function __construct(
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Payment\Model\Method\Logger $paymentLogger
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->logger = $logger;
        $this->paymentLogger = $paymentLogger;
    }

    public function placeRequest(TransferInterface $transferObject): array
    {
        $apiClient = $this->apiClientFactory->create([
            'url' => $transferObject->getUri(),
            'username' => $transferObject->getAuthUsername(),
            'password' => $transferObject->getAuthPassword(),
        ]);

        try {
            $response = $this->apiCall($apiClient, (array) $transferObject->getBody());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            throw new ClientException(__($e->getMessage()));
        } finally {
            $this->paymentLogger->debug([
                'url' => $transferObject->getUri(),
                'username' => $transferObject->getAuthUsername(),
                'request' => $transferObject->getBody(),
                'client' => static::class,
                'response' => $response ?? null,
            ]);
        }

        return $response;
    }

    abstract protected function apiCall(ApiClientInterface $apiClient, array $data): array;
}
