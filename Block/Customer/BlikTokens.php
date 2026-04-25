<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Customer;

use Magento\Vault\Block\Customer\PaymentTokens;
use PayPro\Przelewy24\Api\Data\BlikAliasInterface;

class BlikTokens extends PaymentTokens
{
    public function getType(): string
    {
        return BlikAliasInterface::VAULT_TOKEN_TYPE;
    }
}
