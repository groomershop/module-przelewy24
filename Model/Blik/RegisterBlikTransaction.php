<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use PayPro\Przelewy24\Api\Data\BlikResponseInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\RegisterBlikTransactionInterface;
use PayPro\Przelewy24\Model\RegisterTransaction;

class RegisterBlikTransaction implements RegisterBlikTransactionInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private \Magento\Quote\Api\CartRepositoryInterface $cartRepository;

    /**
     * @var \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface
     */
    private \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var \PayPro\Przelewy24\Model\RegisterTransaction
     */
    private \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction;

    /**
     * @var \PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory
     */
    private \PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory $blikResponseFactory;

    /**
     * @var \PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder
     */
    private \PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder $blikPSURequestBuilder;

    /**
     * @var \PayPro\Przelewy24\Model\Blik\ChargeBlik
     */
    private \PayPro\Przelewy24\Model\Blik\ChargeBlik $chargeBlik;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        \PayPro\Przelewy24\Model\RegisterTransaction $registerTransaction,
        \PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory $blikResponseFactory,
        \PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder $blikPSURequestBuilder,
        \PayPro\Przelewy24\Model\Blik\ChargeBlik $chargeBlik
    ) {
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->registerTransaction = $registerTransaction;
        $this->blikResponseFactory = $blikResponseFactory;
        $this->blikPSURequestBuilder = $blikPSURequestBuilder;
        $this->chargeBlik = $chargeBlik;
    }

    public function execute(string $cartId, string $blikCode, bool $saveAlias = false): BlikResponseInterface
    {
        if (!is_numeric($cartId)) {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        }
        /** @var \Magento\Quote\Model\Quote $cart */
        $cart = $this->cartRepository->getActive((int) $cartId);
        $storeId = (int) $cart->getStoreId();

        $additionalPayload = $this->blikPSURequestBuilder->build([]);
        $additionalPayload[TransactionPayloadInterface::URL_CARD_PAYMENT_NOTIFICATION]
            = $cart->getStore()->getUrl(TransactionPayloadInterface::BLIK_NOTIFICATION_ROUTE);

        if ($saveAlias) {
            $additionalPayload[TransactionPayloadInterface::REFERENCE_REGISTER] = true;
        }

        [
            RegisterTransaction::RESPONSE => $response,
            RegisterTransaction::PAYLOAD => $payload,
        ] = $this->registerTransaction->execute((string) $cartId, $additionalPayload);

        $chargeResponse = $this->chargeBlik->execute([
            ChargeBlik::TOKEN => $response['token'],
            ChargeBlik::BLIK_CODE => $blikCode,
        ], $storeId);

        return $this->blikResponseFactory->create([
            BlikResponseInterface::SUCCESS => !isset($chargeResponse['error']),
            BlikResponseInterface::MESSAGE => isset($chargeResponse['error'])
                ? (string) __('Transaction has been declined. Please try again later.')
                : (string) __('Confirm payment in banking application.'),
            BlikResponseInterface::SESSION_ID => $payload[TransactionPayloadInterface::SESSION_ID],
        ]);
    }
}
