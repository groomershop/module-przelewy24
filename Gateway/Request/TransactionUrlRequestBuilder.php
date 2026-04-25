<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;

class TransactionUrlRequestBuilder implements BuilderInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->subjectReader = $subjectReader;
        $this->storeManager = $storeManager;
    }

    public function build(array $buildSubject): array
    {
        $storeId = $this->subjectReader->readOrderStoreId($buildSubject);
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            TransactionPayloadInterface::URL_RETURN => $store->getUrl(
                $payment->getData(
                    TransactionPayloadInterface::PAYMENT_RETURN_ROUTE
                ) ?: TransactionPayloadInterface::RETURN_ROUTE
            ),
            TransactionPayloadInterface::URL_STATUS => $store->getUrl(TransactionPayloadInterface::STATUS_ROUTE),
        ];
    }
}
