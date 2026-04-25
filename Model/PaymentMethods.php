<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Exception\LocalizedException;
use PayPro\Przelewy24\Api\PaymentMethodsInterface;

class PaymentMethods implements PaymentMethodsInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiPaymentMethods
     */
    private $apiPaymentMethods;

    /**
     * @var \PayPro\Przelewy24\Model\LanguageResolver
     */
    private $languageResolver;

    /**
     * @var \PayPro\Przelewy24\Api\PaymentMethodsModifierInterface
     */
    private $paymentMethodsModifier;

    /**
     * @var \PayPro\Przelewy24\Model\InstalmentFilter
     */
    private $instalmentFilter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Model\Api\ApiPaymentMethods $apiPaymentMethods,
        \PayPro\Przelewy24\Model\LanguageResolver $languageResolver,
        \PayPro\Przelewy24\Api\PaymentMethodsModifierInterface $paymentMethodsModifier,
        \PayPro\Przelewy24\Model\InstalmentFilter $instalmentFilter,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->apiConfig = $apiConfig;
        $this->apiPaymentMethods = $apiPaymentMethods;
        $this->languageResolver = $languageResolver;
        $this->paymentMethodsModifier = $paymentMethodsModifier;
        $this->instalmentFilter = $instalmentFilter;
        $this->logger = $logger;
    }

    public function execute(?float $amount = null): array
    {
        $paymentMethods = $this->apiPaymentMethods->execute(
            $this->languageResolver->resolve(),
            $this->apiConfig->get()
        );

        if ($amount !== null) {
            $paymentMethods = $this->instalmentFilter->execute($paymentMethods, $amount);
        }

        try {
            return $this->paymentMethodsModifier->modify($paymentMethods);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return [];
    }
}
