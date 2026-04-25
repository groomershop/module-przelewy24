<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Card;

use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class CreateCardPaymentToken
{
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var \Magento\Vault\Api\PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    public function __construct(
        \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Magento\Vault\Api\PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    public function execute(string $refId, CardDetails $cardDetails, int $customerId): ?PaymentTokenInterface
    {
        $gatewayToken = $refId;
        $paymentToken = $this->paymentTokenManagement->getByGatewayToken(
            $gatewayToken,
            ConfigProvider::CARD_CODE,
            $customerId
        );

        if ($paymentToken === null) {
            $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        }

        $paymentToken->setGatewayToken($gatewayToken);
        $paymentToken->setExpiresAt($cardDetails->getTokenExpirationDate());
        $paymentToken->setTokenDetails($cardDetails->toJson());

        if ($paymentToken->getEntityId()) {
            $paymentToken->setIsActive(true);
            $paymentToken->setIsVisible(true);
            $this->paymentTokenRepository->save($paymentToken);
        }

        return $paymentToken;
    }
}
