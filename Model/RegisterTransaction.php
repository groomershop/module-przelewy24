<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Model\Api\ApiConfig;

class RegisterTransaction
{
    const RESPONSE = 'response';
    const PAYLOAD = 'payload';

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientFactory;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \PayPro\Przelewy24\Model\TransactionPayloadTransformer
     */
    private $transactionPayloadTransformer;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $paymentLogger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $client;

    /**
     * @var string
     */
    private $paymentMethod;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Model\TransactionPayloadTransformer $transactionPayloadTransformer,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \Psr\Log\LoggerInterface $logger,
        string $client,
        string $paymentMethod
    ) {
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->apiClientFactory = $apiClientFactory;
        $this->apiConfig = $apiConfig;
        $this->transactionPayloadTransformer = $transactionPayloadTransformer;
        $this->paymentLogger = $paymentLogger;
        $this->logger = $logger;
        $this->client = $client;
        $this->paymentMethod = $paymentMethod;
    }

    public function execute(string $cartId, ?array $additionalPayload = null): array
    {
        if (!is_numeric($cartId)) {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        }

        /** @var \Magento\Quote\Model\Quote $cart */
        $cart = $this->cartRepository->getActive((int) $cartId);
        $transactionPayload = $this->transactionPayloadTransformer->fromCart(
            $cart,
            $additionalPayload ?? [],
            $this->paymentMethod
        );
        $this->cartRepository->save($cart);

        return $this->apiRegister($transactionPayload, (int) $cart->getStoreId());
    }

    public function executeForOrder(Order $order, ?array $additionalPayload = null): array
    {
        $transactionPayload = $this->transactionPayloadTransformer->fromOrder($order, $additionalPayload ?? []);

        return $this->apiRegister($transactionPayload, (int) $order->getStoreId());
    }

    private function apiRegister(array $transactionPayload, int $storeId): array
    {
        $clientConfig = $this->apiConfig->get(ScopeInterface::SCOPE_STORE, $storeId);
        $apiClient = $this->apiClientFactory->create($clientConfig);

        try {
            $response = $apiClient->registerTransaction($transactionPayload);

            if (empty($response['data']['token'])) {
                throw new LocalizedException(__('Przelewy24 error: %1', 'transaction token is missing'));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            throw new LocalizedException(__('Transaction has been declined. Please try again later.'));
        } finally {
            $this->paymentLogger->debug([
                'url' => $clientConfig[ApiConfig::URL],
                'username' => $clientConfig[ApiConfig::USERNAME],
                'request' => $transactionPayload,
                'client' => $this->client,
                'response' => $response ?? null,
            ]);
        }

        return [
            self::RESPONSE => $response['data'],
            self::PAYLOAD => $transactionPayload,
        ];
    }
}
