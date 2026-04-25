<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use PayPro\Przelewy24\Model\Api\ApiAmount;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class RefundTransactionRequestBuilder implements BuilderInterface
{
    private const REQUEST_ID = 'requestId';
    private const REFUNDS = 'refunds';
    private const REFUNDS_UUID = 'refundsUuid';
    private const URL_STATUS = 'urlStatus';
    private const ORDER_ID = 'orderId';
    private const SESSION_ID = 'sessionId';
    private const AMOUNT = 'amount';
    private const DESCRIPTION = 'description';

    private const STATUS_ROUTE = 'przelewy24/status/refund';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \PayPro\Przelewy24\Api\SessionId\RefundsUuIdProviderInterface
     */
    private $refundsUuIdProvider;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityGenerator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PayPro\Przelewy24\Api\SessionId\RefundsUuIdProviderInterface $refundsUuIdProvider
    ) {
        $this->subjectReader = $subjectReader;
        $this->identityGenerator = $identityGenerator;
        $this->storeManager = $storeManager;
        $this->refundsUuIdProvider = $refundsUuIdProvider;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $parentTransaction = $payment->getAuthorizationTransaction();
        if ($parentTransaction === false) {
            throw new CommandException(__('Missing transaction'));
        }

        $refundTransactionId = $this->refundsUuIdProvider->get();
        $payment->setTransactionId($refundTransactionId);

        $transaction = new ApiTransaction(
            $parentTransaction->getAdditionalInformation(Transaction::RAW_DETAILS)
        );

        $amount = new ApiAmount($this->subjectReader->readAmount($buildSubject));
        $storeId = $this->subjectReader->readOrderStoreId($buildSubject);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);

        return [
            self::REQUEST_ID => $this->identityGenerator->generateId(),
            self::REFUNDS => [
                [
                    self::ORDER_ID => $transaction->getOrderId(),
                    self::SESSION_ID => $transaction->getSessionId(),
                    self::AMOUNT => $amount->format(),
                    self::DESCRIPTION => (string) __(
                        'Order: #%1',
                        $this->subjectReader->readOrderIncrementId($buildSubject)
                    ),
                ],
            ],
            self::REFUNDS_UUID => $refundTransactionId,
            self::URL_STATUS => $store->getBaseUrl() . self::STATUS_ROUTE,
        ];
    }
}
