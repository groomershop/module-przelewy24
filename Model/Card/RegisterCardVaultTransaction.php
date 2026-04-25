<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Card;

use Magento\Payment\Gateway\Command\CommandException;
use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\RegisterCardVaultTransactionInterface;
use PayPro\Przelewy24\Model\RegisterTransaction;

class RegisterCardVaultTransaction implements RegisterCardVaultTransactionInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CardConfig
     */
    private $config;

    /**
     * @var \PayPro\Przelewy24\Model\RegisterTransaction
     */
    private $registerTransaction;

    /**
     * @var \PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory
     */
    private $tokenTransactionFactory;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\CardConfig $config,
        \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction,
        \PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory $tokenTransactionFactory,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->config = $config;
        $this->registerTransaction = $registerTransaction;
        $this->tokenTransactionFactory = $tokenTransactionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    public function execute(int $customerId, string $cartId, string $hash): TokenTransactionInterface
    {
        $paymentToken = $this->paymentTokenManagement->getByPublicHash($hash, $customerId);

        if ($paymentToken === null || !$paymentToken->getIsActive()) {
            throw new CommandException(__('Saved payment not found.'));
        }

        [
            RegisterTransaction::RESPONSE => $response,
            RegisterTransaction::PAYLOAD => $payload,
        ] = $this->registerTransaction->execute(
            $cartId,
            [
                TransactionPayloadInterface::CARD_DATA => [
                    TransactionPayloadInterface::MEANS => [
                        TransactionPayloadInterface::REFERENCE_NUMBER => [
                            TransactionPayloadInterface::ID => $paymentToken->getGatewayToken(),
                        ],
                    ],
                    TransactionPayloadInterface::TRANSACTION_TYPE => TransactionPayloadInterface::TYPE_1CLICK,
                ],
                TransactionPayloadInterface::METHOD => $this->config->getMethodId(),
            ]
        );

        return $this->tokenTransactionFactory->create([
            TokenTransactionInterface::TOKEN => $response['token'],
            TokenTransactionInterface::TRANSACTION => $payload[TransactionPayloadInterface::SESSION_ID],
        ]);
    }
}
