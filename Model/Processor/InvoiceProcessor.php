<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Processor;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;

class InvoiceProcessor
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
    }

    public function process(Payment $payment): void
    {
        try {
            $invoice = $payment->getCreatedInvoice();
            if ($invoice instanceof Invoice) {
                $this->invoiceSender->send($invoice);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}
