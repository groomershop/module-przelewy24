<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;

class CardVaultRequestBuilder implements BuilderInterface
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
            $payment->getAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH),
            $payment->getAdditionalInformation(PaymentTokenInterface::CUSTOMER_ID)
        );

        if ($paymentToken === null || !$paymentToken->getIsActive()) {
            throw new CommandException(\__('Saved payment not found.'));
        }

        return [
            TransactionPayloadInterface::CARD_DATA => [
                TransactionPayloadInterface::MEANS => [
                    TransactionPayloadInterface::REFERENCE_NUMBER => [
                        TransactionPayloadInterface::ID => $paymentToken->getGatewayToken(),
                    ],
                ],
                TransactionPayloadInterface::TRANSACTION_TYPE => TransactionPayloadInterface::TYPE_1CLICK,
            ],
        ];
    }
}
