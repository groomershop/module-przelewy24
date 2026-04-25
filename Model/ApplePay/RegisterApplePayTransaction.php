<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\ApplePay;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\RegisterApplePayTransactionInterface;
use PayPro\Przelewy24\Model\RegisterTransaction;

class RegisterApplePayTransaction implements RegisterApplePayTransactionInterface
{
    const TYPE = 'applepay';

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\ApplePayConfig
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
        \PayPro\Przelewy24\Gateway\Config\ApplePayConfig $config,
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
            RegisterTransaction::PAYLOAD => $payload,
            RegisterTransaction::RESPONSE => $response,
        ] = $this->registerTransaction->execute(
            $cartId,
            $additionalPayload
        );

        return $this->tokenTransactionFactory->create([
            TokenTransactionInterface::TRANSACTION => $payload[TransactionPayloadInterface::SESSION_ID],
            TokenTransactionInterface::TOKEN => $response['token'],
        ]);
    }
}
