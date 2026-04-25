<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;

class ApiPaymentMethods
{
    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientInterfaceFactory;

    /**
     * @var \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterfaceFactory
     */
    private $apiPaymentMethodInterfaceFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientInterfaceFactory,
        \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterfaceFactory $apiPaymentMethodInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->apiClientInterfaceFactory = $apiClientInterfaceFactory;
        $this->apiPaymentMethodInterfaceFactory = $apiPaymentMethodInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param string $lang
     * @param array $apiConfig
     * @return \PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface[]
     */
    public function execute(string $lang, array $apiConfig): array
    {
        $paymentMethods = [];
        $apiClient = $this->apiClientInterfaceFactory->create($apiConfig);
        foreach ($apiClient->paymentMethods($lang) as $method) {
            $paymentMethod = $this->apiPaymentMethodInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $paymentMethod,
                $this->mapCamelCaseFields($method),
                ApiPaymentMethodInterface::class
            );

            $paymentMethods[] = $paymentMethod;
        }

        return $paymentMethods;
    }

    private function mapCamelCaseFields(array $method): array
    {
        $method['img_url'] = $method['imgUrl'] ?? null;
        $method['mobile_img_url'] = $method['mobileImgUrl'] ?? null;

        return $method;
    }
}
