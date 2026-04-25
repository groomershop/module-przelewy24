<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use PayPro\Przelewy24\Api\Data\BlikAliasInterface;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class CreateBlikPaymentToken
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

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    public function __construct(
        \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Magento\Vault\Api\PaymentTokenRepositoryInterface $paymentTokenRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->serializer = $serializer;
    }

    public function execute(BlikAliasInterface $blikAlias, int $customerId): PaymentTokenInterface
    {
        $paymentToken = $this->paymentTokenManagement->getByGatewayToken(
            $blikAlias->getValue(),
            ConfigProvider::BLIK_CODE,
            $customerId
        );

        if ($paymentToken) {
            $paymentToken->setIsActive(true);
            $paymentToken->setIsVisible(true);
            $this->paymentTokenRepository->save($paymentToken);

            return $paymentToken;
        }

        $paymentToken = $this->paymentTokenFactory->create(BlikAliasInterface::VAULT_TOKEN_TYPE);
        $paymentToken->setGatewayToken($blikAlias->getValue());
        $paymentToken->setTokenDetails((string) $this->serializer->serialize([
            'email' => $blikAlias->getValue(),
            'label' => $blikAlias->getLabel(),
        ]));

        $expiryDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $expiryDate->add(new \DateInterval('P1Y'));
        $paymentToken->setExpiresAt($expiryDate->format('Y-m-d 00:00:00'));

        return $paymentToken;
    }
}
