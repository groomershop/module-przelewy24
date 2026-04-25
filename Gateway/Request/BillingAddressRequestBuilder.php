<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

class BillingAddressRequestBuilder implements BuilderInterface
{
    private const EMAIL = 'email';
    private const CLIENT = 'client';
    private const ADDRESS = 'address';
    private const ZIP = 'zip';
    private const CITY = 'city';
    private const COUNTRY = 'country';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    public function build(array $buildSubject): array
    {
        $payment = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Payment\Gateway\Data\AddressAdapterInterface|\Magento\Sales\Api\Data\OrderAddressInterface|null $billingAddress */
        $billingAddress = $payment->getOrder()->getBillingAddress();

        if ($billingAddress instanceof AddressAdapterInterface) {
            return [
                self::EMAIL => $billingAddress->getEmail(),
                self::CLIENT => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
                self::ADDRESS => $billingAddress->getStreetLine1() . ' ' . $billingAddress->getStreetLine2(),
                self::ZIP => $billingAddress->getPostcode(),
                self::CITY => $billingAddress->getCity(),
                self::COUNTRY => $billingAddress->getCountryId(),
            ];
        }

        if ($billingAddress instanceof OrderAddressInterface) {
            /**
             * Compatibility with modules rewriting order adapter
             * @see \Magento\Payment\Gateway\Data\OrderAdapterInterface
             */
            return [
                self::EMAIL => $billingAddress->getEmail(),
                self::CLIENT => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
                self::ADDRESS => implode(' ', $billingAddress->getStreet() ?? []),
                self::ZIP => $billingAddress->getPostcode(),
                self::CITY => $billingAddress->getCity(),
                self::COUNTRY => $billingAddress->getCountryId(),
            ];
        }

        throw new CommandException(__('Billing address is missing'));
    }
}
