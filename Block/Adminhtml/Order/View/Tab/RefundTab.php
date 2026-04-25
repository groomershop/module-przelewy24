<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Model\Api\ApiConfig;

/**
 * This block allows making refunds for orders paid with Dialcom_Przelewy module
 *
 * @see \PayPro\Przelewy24\Controller\Adminhtml\Payment\Refund
 */
class RefundTab extends Template implements TabInterface
{
    const STATUSES = [
        1 => 'Completed',
        2 => 'Waiting for completion',
        3 => 'Waiting for Przelewy24 approval',
        4 => 'Rejected',
    ];

    /** @var string  */
    protected $_template = 'PayPro_Przelewy24::order/view/refund.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \PayPro\Przelewy24\Model\Config\RefundCompatibilityConfig
     */
    private $refundCompatibilityConfig;

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
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var array|null
     */
    private $refunds;

    /**
     * @var int|null
     */
    private $p24OrderId;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \PayPro\Przelewy24\Model\Config\RefundCompatibilityConfig $refundCompatibilityConfig,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientInterfaceFactory,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = [],
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null,
        ?\Magento\Directory\Helper\Data $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->registry = $registry;
        $this->refundCompatibilityConfig = $refundCompatibilityConfig;
        $this->apiConfig = $apiConfig;
        $this->apiClientInterfaceFactory = $apiClientInterfaceFactory;
        $this->paymentLogger = $paymentLogger;
        $this->priceCurrency = $priceCurrency;
    }

    public function getTabLabel(): string
    {
        return (string) __('Przelewy24 refunds');
    }

    public function getTabTitle(): string
    {
        return (string) __('Przelewy24 refunds');
    }

    public function canShowTab(): bool
    {
        return $this->refundCompatibilityConfig->isEnabled() && $this->getSessionId();
    }

    public function isHidden(): bool
    {
        return false;
    }

    public function getRefundSubmitUrl(): string
    {
        return $this->getUrl('przelewy24/payment/refund');
    }

    public function getOrder(): Order
    {
        $order = $this->registry->registry('current_order');
        if (!$order instanceof Order) {
            throw new LocalizedException(__('Invalid order'));
        }

        return $order;
    }

    public function getSessionId(): ?string
    {
        return $this->getOrder()->getData('p24_session_id');
    }

    public function getRefundsList(): array
    {
        if ($this->getSessionId() === null) {
            return [];
        }

        if ($this->refunds === null) {
            $clientConfig = $this->apiConfig->get(ScopeInterface::SCOPE_STORE, (int) $this->getOrder()->getStoreId());
            $apiClient = $this->apiClientInterfaceFactory->create($clientConfig);
            $response = $apiClient->transactionStatus($this->getSessionId());
            $this->paymentLogger->debug([
                'url' => $clientConfig[ApiConfig::URL],
                'username' => $clientConfig[ApiConfig::USERNAME],
                'request' => [],
                'client' => self::class,
                'response' => $response,
            ]);
            $this->p24OrderId = $response['data']['orderId'] ?? null;
            if (!$this->p24OrderId) {
                return [];
            }

            $refundInfo = $apiClient->refundInfo($this->p24OrderId);
            $this->paymentLogger->debug([
                'url' => sprintf($clientConfig[ApiConfig::URL], $this->p24OrderId),
                'username' => $clientConfig[ApiConfig::USERNAME],
                'request' => [],
                'client' => self::class,
                'response' => $refundInfo,
            ]);

            $this->refunds = $refundInfo['data']['refunds'] ?? [];
        }

        return $this->refunds;
    }

    public function getP24OrderId(): ?int
    {
        return $this->p24OrderId;
    }

    public function formatRefundDate(string $date): ?string
    {
        $dt = \DateTime::createFromFormat('YmdHis', $date, new \DateTimeZone('Europe/Warsaw'));

        return $dt === false ? null : $this->formatDate($dt, \IntlDateFormatter::SHORT, true);
    }

    public function formatAmount(int $amount): string
    {
        return $this->priceCurrency->format(
            $amount / 100,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getOrder()->getStoreId()
        );
    }

    public function formatStatus(int $status): string
    {
        return self::STATUSES[$status] ?? 'Unknown';
    }
}
