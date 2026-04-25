<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Model\PaymentLink;
use Psr\Log\LoggerInterface;

class SendLink extends Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \PayPro\Przelewy24\Model\PaymentLink
     */
    private $paymentLink;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        PaymentLink $paymentLink,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->paymentLink = $paymentLink;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute(): ResultInterface
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $order = $this->orderRepository->get($orderId);
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $storeId = (int) $order->getStoreId();

            $link = $this->paymentLink->execute($payment);

            $billingAddress = $order->getBillingAddress();
            $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('przelewy24_payment_link')
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars([
                    'order' => $order,
                    'payment_link' => $link,
                    'customer_name' => $customerName,
                ])
                ->setFrom([
                    'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE, $storeId),
                    'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE, $storeId),
                ])
                ->addTo($order->getCustomerEmail(), $customerName)
                ->getTransport();

            $transport->sendMessage();

            $order->addCommentToStatusHistory(
                (string) __('Payment link sent to %1 (%2).', $customerName, $order->getCustomerEmail())
            );
            $this->orderRepository->save($order);

            $this->messageManager->addSuccessMessage(__('Payment link has been sent to the customer.'));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('Unable to send payment link. Please try again later.'));
        }

        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
