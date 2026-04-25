<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class Review extends Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \PayPro\Przelewy24\Model\UpdatePaymentByTransactions
     */
    private $updatePaymentByTransactions;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \PayPro\Przelewy24\Model\UpdatePaymentByTransactions $updatePaymentByTransactions,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->updatePaymentByTransactions = $updatePaymentByTransactions;
        $this->logger = $logger;
    }

    public function execute(): ?ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderId = (int) $this->getRequest()->getParam('order_id');

        try {
            $order = $this->orderRepository->get($orderId);
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            if (strpos($payment->getMethod(), ConfigProvider::CODE) === false) {
                throw new LocalizedException(__('Invalid P24 payment method'));
            }

            $this->updatePaymentByTransactions->execute($payment);

            $this->orderRepository->save($order);
            $this->messageManager->addSuccessMessage((string) $this->getSuccessMessage($payment));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage((string) __('We can\'t update the payment right now.'));
            $this->logger->error('P24 review action error', ['exception' => $e]);
        }

        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);

        return $resultRedirect;
    }

    private function getSuccessMessage(\Magento\Sales\Model\Order\Payment $payment): \Magento\Framework\Phrase
    {
        if ($payment->getIsTransactionApproved()) {
            return __('Transaction has been approved.');
        }

        if ($payment->getIsTransactionDenied()) {
            return __('Transaction has been voided/declined.');
        }

        return __('There is no update for the transaction.');
    }
}
