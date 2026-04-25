<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;

class TransactionPayloadTransformer
{
    /**
     * @var \PayPro\Przelewy24\Api\Data\TransactionPayloadInterfaceFactory
     */
    private $transactionPayloadFactory;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $commonConfig;

    /**
     * @var \PayPro\Przelewy24\Model\LanguageResolver
     */
    private $languageResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface
     */
    private $sessionIdProvider;

    public function __construct(
        \PayPro\Przelewy24\Api\Data\TransactionPayloadInterfaceFactory $transactionPayloadFactory,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $commonConfig,
        \PayPro\Przelewy24\Model\LanguageResolver $languageResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface $sessionIdProvider
    ) {
        $this->transactionPayloadFactory = $transactionPayloadFactory;
        $this->commonConfig = $commonConfig;
        $this->languageResolver = $languageResolver;
        $this->storeManager = $storeManager;
        $this->sessionIdProvider = $sessionIdProvider;
    }

    public function fromCart(Quote $quote, array $additionalPayload = [], string $fallbackPaymentMethod = ''): array
    {
        $quote->reserveOrderId();

        $storeId = (int) $quote->getStoreId();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);
        $sessionId = $this->sessionIdProvider->get(
            $quote->getPayment()->getMethod() ?? $fallbackPaymentMethod, // @phpstan-ignore-line
            $quote->getPayment()->getAdditionalInformation()
        );

        $billingAddress = $quote->getBillingAddress();
        $client = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        $address = ($billingAddress->getStreet()[0] ?? '') . ' ' . ($billingAddress->getStreet()[1] ?? '');

        $transactionPayload = $this->transactionPayloadFactory->create(['data' => array_merge([
            TransactionPayloadInterface::MERCHANT_ID => $this->commonConfig->getMerchantId($storeId),
            TransactionPayloadInterface::POS_ID => $this->commonConfig->getPosId($storeId),
            TransactionPayloadInterface::SESSION_ID => $sessionId,
            TransactionPayloadInterface::AMOUNT => $quote->getBaseGrandTotal(),
            TransactionPayloadInterface::CURRENCY => $quote->getBaseCurrencyCode(),
            TransactionPayloadInterface::DESCRIPTION => (string) __('Order: #%1', $quote->getReservedOrderId()),
            TransactionPayloadInterface::ENCODING => ApiClientInterface::ENCODING,
            TransactionPayloadInterface::EMAIL => $billingAddress->getEmail(),
            TransactionPayloadInterface::CLIENT => $client,
            TransactionPayloadInterface::ADDRESS => $address,
            TransactionPayloadInterface::ZIP => $billingAddress->getPostcode(),
            TransactionPayloadInterface::CITY => $billingAddress->getCity(),
            TransactionPayloadInterface::COUNTRY => $billingAddress->getCountryId(),
            TransactionPayloadInterface::LANGUAGE => $this->languageResolver->resolve($storeId),
            TransactionPayloadInterface::URL_RETURN => $store->getUrl(TransactionPayloadInterface::RETURN_ROUTE),
            TransactionPayloadInterface::URL_STATUS => $store->getUrl(TransactionPayloadInterface::STATUS_ROUTE),
        ], $additionalPayload)]);

        return $transactionPayload->get($this->commonConfig->getCrcKey($storeId));
    }

    public function fromOrder(Order $order, array $additionalPayload = []): array
    {
        if (!$order->getPayment()) {
            throw new LocalizedException(__('Payment for order not found.'));
        }

        $storeId = (int) $order->getStoreId();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);
        $sessionId = $this->sessionIdProvider->get(
            $order->getPayment()->getMethod() ?? '', // @phpstan-ignore-line
            $order->getPayment()->getAdditionalInformation()
        );

        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress instanceof OrderAddressInterface) {
            throw new LocalizedException(__('Billing address is missing'));
        }

        $client = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        $address = ($billingAddress->getStreet()[0] ?? '') . ' ' . ($billingAddress->getStreet()[1] ?? '');

        $transactionPayload = $this->transactionPayloadFactory->create(['data' => array_merge([
            TransactionPayloadInterface::MERCHANT_ID => $this->commonConfig->getMerchantId($storeId),
            TransactionPayloadInterface::POS_ID => $this->commonConfig->getPosId($storeId),
            TransactionPayloadInterface::SESSION_ID => $sessionId,
            TransactionPayloadInterface::AMOUNT => $order->getBaseGrandTotal(),
            TransactionPayloadInterface::CURRENCY => $order->getBaseCurrencyCode(),
            TransactionPayloadInterface::DESCRIPTION => (string) __('Order: #%1', $order->getIncrementId()),
            TransactionPayloadInterface::ENCODING => ApiClientInterface::ENCODING,
            TransactionPayloadInterface::EMAIL => $billingAddress->getEmail(),
            TransactionPayloadInterface::CLIENT => $client,
            TransactionPayloadInterface::ADDRESS => $address,
            TransactionPayloadInterface::ZIP => $billingAddress->getPostcode(),
            TransactionPayloadInterface::CITY => $billingAddress->getCity(),
            TransactionPayloadInterface::COUNTRY => $billingAddress->getCountryId(),
            TransactionPayloadInterface::LANGUAGE => $this->languageResolver->resolve($storeId),
            TransactionPayloadInterface::URL_RETURN => $store->getUrl(TransactionPayloadInterface::RETURN_ROUTE),
            TransactionPayloadInterface::URL_STATUS => $store->getUrl(TransactionPayloadInterface::STATUS_ROUTE),
        ], $additionalPayload)]);

        return $transactionPayload->get($this->commonConfig->getCrcKey($storeId));
    }
}
