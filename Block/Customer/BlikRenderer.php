<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractTokenRenderer;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class BlikRenderer extends AbstractTokenRenderer
{
    public function getIconUrl(): string
    {
        return $this->getViewFileUrl('PayPro_Przelewy24::images/logo-blik.svg');
    }

    public function getIconHeight(): int
    {
        return 20;
    }

    public function getIconWidth(): int
    {
        return 40;
    }

    public function canRender(PaymentTokenInterface $token): bool
    {
        return $token->getPaymentMethodCode() === ConfigProvider::BLIK_CODE;
    }

    public function getTokenEmail(): string
    {
        return $this->getTokenDetails()['email'] ?? '';
    }
}
