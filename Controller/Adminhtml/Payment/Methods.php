<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;
use PayPro\Przelewy24\Model\LanguageResolver;

class Methods extends Action implements HttpGetActionInterface
{
    const SCOPE_TYPE_PARAM = 'scopeType';
    const SCOPE_ID_PARAM = 'scopeId';

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiPaymentMethods
     */
    private $apiPaymentMethods;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig,
        \PayPro\Przelewy24\Model\Api\ApiPaymentMethods $apiPaymentMethods,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        parent::__construct($context);
        $this->apiConfig = $apiConfig;
        $this->apiPaymentMethods = $apiPaymentMethods;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    public function execute(): ResultInterface
    {
        $request = $this->getRequest();
        $scopeType = $request->getParam(self::SCOPE_TYPE_PARAM, 'default');
        $scopeId = $request->getParam(self::SCOPE_ID_PARAM);
        $scopeId = empty($scopeId) ? null : (int) $scopeId;

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $data = [];
        try {
            $paymentMethods = $this->apiPaymentMethods->execute(
                LanguageResolver::DEFAULT_LANGUAGE,
                $this->apiConfig->get($scopeType, $scopeId)
            );

            foreach ($paymentMethods as $method) {
                if ($method->getId() === ApiPaymentMethodInterface::ERATY_SCB_ID) {
                    $method->setIsStandalone(true);
                }
                $data[] = $this->dataObjectProcessor->buildOutputDataArray(
                    $method,
                    ApiPaymentMethodInterface::class
                );
            }

            $result->setData($data);
        } catch (\Exception $e) {
            $result->setData([]);
        }

        return $result;
    }
}
