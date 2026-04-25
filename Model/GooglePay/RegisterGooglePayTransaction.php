<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\GooglePay;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\RegisterGooglePayTransactionInterface;
use PayPro\Przelewy24\Model\RegisterTransaction;

class RegisterGooglePayTransaction implements RegisterGooglePayTransactionInterface
{
    public const TYPE = 'googlepay';

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\GooglePayConfig
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
        \PayPro\Przelewy24\Gateway\Config\GooglePayConfig $config,
        \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction,
        \PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory $tokenTransactionFactory
    ) {
        $this->config = $config;
        $this->registerTransaction = $registerTransaction;
        $this->tokenTransactionFactory = $tokenTransactionFactory;
    }

    public function execute(string $cartId, string $tokenObject, ?string $returnUrl = null): TokenTransactionInterface
    {
        $additionalPayload = [
            TransactionPayloadInterface::CARD_DATA => [
                TransactionPayloadInterface::MEANS => [
                    TransactionPayloadInterface::X_PAY_PAYLOAD => [
                        TransactionPayloadInterface::PAYLOAD => base64_encode($tokenObject),
                        TransactionPayloadInterface::TYPE => self::TYPE,
                    ],
                ],
            ],
            TransactionPayloadInterface::METHOD => $this->config->getMethodId(),
        ];
        if ($returnUrl) {
            $additionalPayload[TransactionPayloadInterface::URL_RETURN] = $returnUrl;
        }

        [
            RegisterTransaction::RESPONSE => $response,
            RegisterTransaction::PAYLOAD => $payload,
        ] = $this->registerTransaction->execute(
            $cartId,
            $additionalPayload
        );

        return $this->tokenTransactionFactory->create([
            TokenTransactionInterface::TOKEN => $response['token'],
            TokenTransactionInterface::TRANSACTION => $payload[TransactionPayloadInterface::SESSION_ID],
        ]);
    }
}
