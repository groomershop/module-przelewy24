<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use PayPro\Przelewy24\Api\Data\BlikAliasInterface;

class BlikAliasResolver
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Laminas\Uri\Uri
     */
    private $uri;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Laminas\Uri\Uri $uri
    ) {
        $this->subjectReader = $subjectReader;
        $this->storeManager = $storeManager;
        $this->uri = $uri;
    }

    public function resolve(array $subject): BlikAliasInterface
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \Magento\Payment\Gateway\Data\AddressAdapterInterface|\Magento\Sales\Api\Data\OrderAddressInterface|null $billingAddress */
        $billingAddress = $paymentDO->getOrder()->getBillingAddress();

        if (!$billingAddress instanceof AddressAdapterInterface && !$billingAddress instanceof OrderAddressInterface) {
            throw new CommandException(__('Billing address is missing'));
        }

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($this->subjectReader->readOrderStoreId($subject));

        return new BlikAlias(
            (string) $this->uri->parse($store->getBaseUrl())->getHost(),
            (string) $billingAddress->getEmail()
        );
    }
}
