<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Card;

use PayPro\Przelewy24\Api\Data\CardTokenizationPayloadInterface;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Api\GetCardTokenizationPayloadInterface;
use PayPro\Przelewy24\Model\Api\ApiSignature;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class GetCardTokenizationPayload implements GetCardTokenizationPayloadInterface
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    /**
     * @var \PayPro\Przelewy24\Api\Data\CardTokenizationPayloadInterfaceFactory
     */
    private $cardTokenizationPayloadFactory;

    /**
     * @var \PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface
     */
    private $sessionIdProvider;

    public function __construct(
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \PayPro\Przelewy24\Api\Data\CardTokenizationPayloadInterfaceFactory $cardTokenizationPayloadFactory,
        \PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface $sessionIdProvider
    ) {
        $this->config = $config;
        $this->cardTokenizationPayloadFactory = $cardTokenizationPayloadFactory;
        $this->sessionIdProvider = $sessionIdProvider;
    }

    public function execute(): CardTokenizationPayloadInterface
    {
        $sessionId = $this->sessionIdProvider->get(ConfigProvider::CARD_CODE);
        $signature = new ApiSignature([
            TransactionPayloadInterface::MERCHANT_ID => $this->config->getMerchantId(),
            TransactionPayloadInterface::SESSION_ID => $sessionId,
        ]);

        return $this->cardTokenizationPayloadFactory->create([
            CardTokenizationPayloadInterface::MERCHANT_ID => $this->config->getMerchantId(),
            CardTokenizationPayloadInterface::SESSION_ID => $sessionId,
            CardTokenizationPayloadInterface::SIGNATURE => $signature->sign($this->config->getCrcKey()),
        ]);
    }
}
