<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use PayPro\Przelewy24\Model\Api\ApiAmount;
use PayPro\Przelewy24\Model\Api\ApiSignature;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class VerifyTransactionRequestBuilder implements BuilderInterface
{
    private const MERCHANT_ID = 'merchantId';
    private const POS_ID = 'posId';
    private const SESSION_ID = 'sessionId';
    private const AMOUNT = 'amount';
    private const CURRENCY = 'currency';
    private const ORDER_ID = 'orderId';
    private const SIGN = 'sign';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $currency = $this->subjectReader->readCurrency($buildSubject);
        $amount = new ApiAmount($this->subjectReader->readOrderAmount($buildSubject));
        $magentoTransaction = $payment->getAuthorizationTransaction();
        if (!$magentoTransaction) {
            throw new CommandException(__('Missing payment parent transaction'));
        }
        $transaction = new ApiTransaction(
            $magentoTransaction->getAdditionalInformation(Transaction::RAW_DETAILS)
        );

        $storeId = $this->subjectReader->readOrderStoreId($buildSubject);

        $merchantId = $this->config->getMerchantId($storeId);
        $posId = $this->config->getPosId($storeId);

        $signature = new ApiSignature([
            self::SESSION_ID => $transaction->getSessionId(),
            self::ORDER_ID => $transaction->getOrderId(),
            self::AMOUNT => $amount->format(),
            self::CURRENCY => $currency,
        ]);

        return [
            self::MERCHANT_ID => $merchantId,
            self::POS_ID => $posId,
            self::SESSION_ID => $transaction->getSessionId(),
            self::AMOUNT => $amount->format(),
            self::CURRENCY => $currency,
            self::ORDER_ID => $transaction->getOrderId(),
            self::SIGN => $signature->sign($this->config->getCrcKey($storeId)),
        ];
    }
}
