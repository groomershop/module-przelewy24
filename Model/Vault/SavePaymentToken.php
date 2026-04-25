<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Vault;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class SavePaymentToken
{
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    public function __construct(
        \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
    }

    public function execute(OrderPaymentInterface $payment, ?PaymentTokenInterface $paymentToken): void
    {
        if ($paymentToken === null) {
            return;
        }

        $extensionAttributes = $payment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setVaultPaymentToken($paymentToken);
    }
}
