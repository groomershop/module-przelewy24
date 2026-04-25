<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class CardRenderer extends AbstractCardRenderer
{
    public function canRender(PaymentTokenInterface $token): bool
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CARD_CODE;
    }

    public function getNumberLast4Digits(): string
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    public function getExpDate(): string
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    public function getIconUrl(): string
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    public function getIconHeight(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    public function getIconWidth(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }

    protected function getTokenDetails(): array
    {
        return parent::getTokenDetails() ?? [];
    }
}
