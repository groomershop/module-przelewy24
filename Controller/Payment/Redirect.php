<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use PayPro\Przelewy24\Model\TransactionUrl;

class Redirect implements HttpGetActionInterface
{
    const FAILURE_PARAM = 'p24_redirect_failure';

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private \Magento\Framework\Message\ManagerInterface $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    public function execute(): ResultInterface
    {
        $result = $this->redirectFactory->create();

        try {
            $order = $this->checkoutSession->getLastRealOrder();
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();

            $result->setUrl($payment->getAdditionalInformation(TransactionUrl::KEY));
        } catch (\Throwable $e) {
            $this->logger->error('Przelewy24 payment redirect error', ['exception' => $e]);
            $this->messageManager->addErrorMessage((string) __('An error occurred while redirecting to Przelewy24.'));
            $result->setPath('checkout/onepage/success', [self::FAILURE_PARAM => 1]);
        }

        return $result;
    }
}
