<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;

class BlikVaultRequestBuilder implements BuilderInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        $paymentToken = $this->paymentTokenManagement->getByPublicHash(
            $payment->getAdditionalInformation('public_hash'),
            $payment->getAdditionalInformation('customer_id')
        );

        if ($paymentToken === null || !$paymentToken->getIsActive()) {
            throw new CommandException(__('Saved payment not found.'));
        }

        return [
            TransactionPayloadInterface::METHOD_REF_ID => $paymentToken->getGatewayToken(),
        ];
    }
}
