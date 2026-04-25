<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class DenyPaymentCommand implements CommandInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Model\DenyPayment
     */
    private $denyPayment;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Model\DenyPayment $denyPayment,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->denyPayment = $denyPayment;
        $this->config = $config;
    }

    /**
     * @param array $commandSubject
     * @return null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $storeId = (int) $paymentDO->getOrder()->getStoreId();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        $magentoTransaction = $payment->getAuthorizationTransaction();
        if ($magentoTransaction === false) {
            throw new CommandException(__('Payment transaction not found'));
        }

        $transaction = new ApiTransaction([
            ApiTransaction::MERCHANT_ID => $this->config->getMerchantId($storeId),
            ApiTransaction::POS_ID => $this->config->getPosId($storeId),
            ApiTransaction::SESSION_ID => $magentoTransaction->getTxnId(),
        ]);

        $this->denyPayment->execute($paymentDO, $transaction);
    }
}
