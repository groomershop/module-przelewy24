<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Observer\GatewayDataAssignObserver;

class AdditionalInformationRequestBuilder implements BuilderInterface
{
    private const METHOD = 'method';
    private const REGULATION_ACCEPT = 'regulationAccept';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $data = [
            self::REGULATION_ACCEPT => (bool) $payment->getAdditionalInformation(
                GatewayDataAssignObserver::REGULATION_ACCEPT
            ),
        ];

        if ($method = $payment->getAdditionalInformation(GatewayDataAssignObserver::METHOD)) {
            $data[self::METHOD] = (int) $method;
        }

        return $data;
    }
}
