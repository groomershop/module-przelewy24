<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CurrencyConfigAwareInterface
     */
    private $config;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \PayPro\Przelewy24\Gateway\Config\CurrencyConfigAwareInterface $config
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
    }

    public function validate(array $validationSubject)
    {
        $storeId = isset($validationSubject['storeId']) ? (int) $validationSubject['storeId'] : null;
        $currencyCode = $validationSubject['currency'] ?? '';

        return $this->createResult($this->config->isCurrencyAllowed($currencyCode, $storeId));
    }
}
