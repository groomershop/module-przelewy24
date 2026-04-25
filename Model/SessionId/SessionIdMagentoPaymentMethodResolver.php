<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\SessionId;

use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class SessionIdMagentoPaymentMethodResolver
{
    const CLICK_TO_PAY_PREFIX = 'ccc2p_';
    const CARD_PREFIX = 'cc_';
    const METHOD_PREFIX_MAP = [
        ConfigProvider::BLIK_CODE => 'b0_',
        ConfigProvider::BLIK_VAULT_CODE => 'b0oc_',
        ConfigProvider::CARD_VAULT_CODE => 'ccoc_',
        ConfigProvider::GOOGLE_PAY_CODE => 'gp_',
        ConfigProvider::APPLE_PAY_CODE => 'ap_',
    ];

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CardConfig
     */
    private $cardConfig;

    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \PayPro\Przelewy24\Gateway\Config\CardConfig $cardConfig
    ) {
        $this->userContext = $userContext;
        $this->cardConfig = $cardConfig;
    }

    public function resolve(string $paymentMethod): string
    {
        if (!empty(self::METHOD_PREFIX_MAP[$paymentMethod])) {
            return self::METHOD_PREFIX_MAP[$paymentMethod];
        }

        if ($paymentMethod === ConfigProvider::CARD_CODE) {
            if ($this->userContext->getUserId() && $this->cardConfig->isC2pEnabled()) {
                return self::CLICK_TO_PAY_PREFIX;
            } elseif (!$this->userContext->getUserId() && $this->cardConfig->isC2pEnabledForGuests()) {
                return self::CLICK_TO_PAY_PREFIX;
            }

            return self::CARD_PREFIX ;
        }

        return '';
    }
}
