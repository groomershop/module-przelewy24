<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\ViewModel\Checkout;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Payment;
use PayPro\Przelewy24\Controller\Payment\Redirect;
use PayPro\Przelewy24\Model\PaymentLink as PaymentLinkModel;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class PaymentLink implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private \Magento\Framework\App\RequestInterface $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private \Magento\Checkout\Model\Session $checkoutSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private \Magento\Framework\UrlInterface $url;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $url,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->url = $url;
        $this->logger = $logger;
    }

    public function isRedirectFailure(): bool
    {
        return !!$this->request->getParam(Redirect::FAILURE_PARAM);
    }

    public function getPaymentLink(): ?string
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();

            if ($this->isPaymentLinkVisible($payment)) {
                return $this->url->getUrl(PaymentLinkModel::PAYMENT_ROUTE, ['id' => $payment->getLastTransId()]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Can\'t generate P24 payment link on checkout success', ['exception' => $e]);
        }

        return null;
    }

    private function isPaymentLinkVisible(Payment $payment): bool
    {
        return $payment->getMethod() === ConfigProvider::CODE && $payment->getLastTransId();
    }
}
