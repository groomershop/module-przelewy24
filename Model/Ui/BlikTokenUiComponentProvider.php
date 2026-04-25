<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Ui;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

class BlikTokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var \Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory
     */
    private $tokenUiComponentFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    public function __construct(
        \Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory $tokenUiComponentFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->tokenUiComponentFactory = $tokenUiComponentFactory;
        $this->serializer = $serializer;
    }

    public function getComponentForToken(PaymentTokenInterface $paymentToken): TokenUiComponentInterface
    {
        $details = (array) $this->serializer->unserialize($paymentToken->getTokenDetails());

        return $this->tokenUiComponentFactory->create([
            'config' => [
                'code' => ConfigProvider::BLIK_VAULT_CODE,
                TokenUiComponentProviderInterface::COMPONENT_DETAILS => [
                    'email' => $details['email'] ?? '',
                ],
                TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
            ],
            'name' => 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-blik-vault',
        ]);
    }
}
