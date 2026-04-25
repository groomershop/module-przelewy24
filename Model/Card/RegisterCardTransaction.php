<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Card;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\RegisterCardTransactionInterface;
use PayPro\Przelewy24\Model\RegisterTransaction;

class RegisterCardTransaction implements RegisterCardTransactionInterface
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

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\CardConfig $config,
        \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction,
        \PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory $tokenTransactionFactory
    ) {
        $this->config = $config;
        $this->registerTransaction = $registerTransaction;
        $this->tokenTransactionFactory = $tokenTransactionFactory;
    }

    public function execute(string $cartId, string $sessionId, string $token): TokenTransactionInterface
    {
        [RegisterTransaction::RESPONSE => $response] = $this->registerTransaction->execute(
            $cartId,
            [
                TransactionPayloadInterface::SESSION_ID => $sessionId,
                TransactionPayloadInterface::CARD_DATA => [
                    TransactionPayloadInterface::MEANS => [
                        TransactionPayloadInterface::REFERENCE_NUMBER => [
                            TransactionPayloadInterface::ID => $token,
                        ],
                    ],
                    TransactionPayloadInterface::TRANSACTION_TYPE => TransactionPayloadInterface::TYPE_STANDARD,
                ],
                TransactionPayloadInterface::METHOD => $this->config->getMethodId(),
            ]
        );

        return $this->tokenTransactionFactory->create([
            TokenTransactionInterface::TOKEN => $response['token'],
            TokenTransactionInterface::TRANSACTION => $sessionId,
        ]);
    }
}
