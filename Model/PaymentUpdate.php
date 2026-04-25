<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\App\Area;

class PaymentUpdate
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \PayPro\Przelewy24\Model\Processor\InvoiceProcessor
     */
    private $invoiceProcessor;

    /**
     * @var \PayPro\Przelewy24\Model\UpdatePaymentByTransactions
     */
    private $updatePaymentByTransactions;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \PayPro\Przelewy24\Model\Processor\InvoiceProcessor $invoiceProcessor,
        \PayPro\Przelewy24\Model\UpdatePaymentByTransactions $updatePaymentByTransactions
    ) {
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->invoiceProcessor = $invoiceProcessor;
        $this->updatePaymentByTransactions = $updatePaymentByTransactions;
    }

    public function execute(int $orderId): void
    {
        $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($orderId) {
            $order = $this->orderRepository->get($orderId);
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();

            $this->updatePaymentByTransactions->execute($payment);

            $this->orderRepository->save($order);
            $this->invoiceProcessor->process($payment);
        });
    }
}
