<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Model\Api\ApiAmount;
use PayPro\Przelewy24\Model\Api\ApiConfig;

class Refund extends Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientInterfaceFactory;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $paymentLogger;

    /**
     * @var \PayPro\Przelewy24\Api\SessionId\RefundsUuIdProviderInterface
     */
    private $refundsUuIdProvider;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityGenerator,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientInterfaceFactory,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \PayPro\Przelewy24\Api\SessionId\RefundsUuIdProviderInterface $refundsUuIdProvider
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->identityGenerator = $identityGenerator;
        $this->apiConfig = $apiConfig;
        $this->apiClientInterfaceFactory = $apiClientInterfaceFactory;
        $this->paymentLogger = $paymentLogger;
        $this->refundsUuIdProvider = $refundsUuIdProvider;
    }

    public function execute(): ?ResultInterface
    {
        $request = $this->getRequest();
        $orderId = (int) $request->getParam('order_id');
        $p24OrderId = $request->getParam('p24_order_id');
        $p24SessionId = $request->getParam('p24_session_id');
        $amount = (float) $request->getParam('p24_refund_amount');
        $apiAmount = new ApiAmount($amount);

        $result = $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage((string) __('Refund can\'t be created, order doesn\'t exist.'));

            return $result;
        }

        $payload = [
            'requestId' => $this->identityGenerator->generateId(),
            'refunds' => [
                [
                    'orderId' => $p24OrderId,
                    'sessionId' => $p24SessionId,
                    'amount' => $apiAmount->format(),
                    'description' => (string) __(
                        'Order: #%1',
                        $order->getIncrementId()
                    ),
                ],
            ],
            'refundsUuid' => $this->refundsUuIdProvider->get(),
        ];

        $clientConfig = $this->apiConfig->get(ScopeInterface::SCOPE_STORE, (int) $order->getStoreId());
        $apiClient = $this->apiClientInterfaceFactory->create($clientConfig);
        $response = $apiClient->refundTransaction($payload);

        $this->paymentLogger->debug([
            'url' => $clientConfig[ApiConfig::URL],
            'username' => $clientConfig[ApiConfig::USERNAME],
            'request' => $payload,
            'client' => self::class,
            'response' => $response,
        ]);

        if (isset($response['error'])) {
            $message = is_string($response['error'])
                ? $response['error']
                : implode('. ', array_column($response['error'], 'message'));

            $this->messageManager->addErrorMessage((string) __('Refund can\'t be created: %1', $message));

            return $result;
        }

        $this->messageManager->addSuccessMessage((string) __('You have refunded %1.', $amount));

        return $result;
    }
}
