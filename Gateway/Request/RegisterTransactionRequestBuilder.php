<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Api\ApiClientInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;

class RegisterTransactionRequestBuilder implements BuilderInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $commonConfig;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @var \PayPro\Przelewy24\Api\Data\TransactionPayloadInterfaceFactory
     */
    private $transactionPayloadFactory;

    /**
     * @var \PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface
     */
    private $sessionIdProvider;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $commonConfig,
        \PayPro\Przelewy24\Gateway\Config\GatewayConfig $gatewayConfig,
        \PayPro\Przelewy24\Api\Data\TransactionPayloadInterfaceFactory $transactionPayloadFactory,
        \PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface $sessionIdProvider
    ) {
        $this->subjectReader = $subjectReader;
        $this->commonConfig = $commonConfig;
        $this->gatewayConfig = $gatewayConfig;
        $this->transactionPayloadFactory = $transactionPayloadFactory;
        $this->sessionIdProvider = $sessionIdProvider;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $orderIncrementId = $this->subjectReader->readOrderIncrementId($buildSubject);
        $sessionId = $payment->getAdditionalInformation(TransactionPayloadInterface::SESSION_ID)
            ?? $this->sessionIdProvider->get(
                $payment->getMethod(),
                $payment->getAdditionalInformation()
            );
        $payment->setTransactionId($sessionId);

        $storeId = $this->subjectReader->readOrderStoreId($buildSubject);
        $waitForResult = $this->gatewayConfig->isWaitForTransactionResultEnabled($storeId);

        $transactionPayload = $this->transactionPayloadFactory->create(['data' => [
            TransactionPayloadInterface::MERCHANT_ID => $this->commonConfig->getMerchantId($storeId),
            TransactionPayloadInterface::POS_ID => $this->commonConfig->getPosId($storeId),
            TransactionPayloadInterface::SESSION_ID => $sessionId,
            TransactionPayloadInterface::AMOUNT => $this->subjectReader->readOrderAmount($buildSubject),
            TransactionPayloadInterface::CURRENCY => $this->subjectReader->readCurrency($buildSubject),
            TransactionPayloadInterface::DESCRIPTION => (string) __('Order: #%1', $orderIncrementId),
            TransactionPayloadInterface::WAIT_FOR_RESULT => $waitForResult,
            TransactionPayloadInterface::ENCODING => ApiClientInterface::ENCODING,
        ]]);

        return $transactionPayload->get($this->commonConfig->getCrcKey($storeId));
    }
}
