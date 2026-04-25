<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Adminhtml\Test;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Access extends Action implements HttpPostActionInterface
{
    /**
     * @var \PayPro\Przelewy24\Api\ApiClientInterfaceFactory
     */
    private $apiClientFactory;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \PayPro\Przelewy24\Api\ApiClientInterfaceFactory $apiClientFactory,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig
    ) {
        parent::__construct($context);
        $this->apiClientFactory = $apiClientFactory;
        $this->apiConfig = $apiConfig;
    }

    public function execute(): ResultInterface
    {
        $request = $this->getRequest();
        $scopeType = $request->getParam('scope_type', 'default');
        $scopeId = $request->getParam('scope_id');
        $scopeId = empty($scopeId) ? null : (int) $scopeId;

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $api = $this->apiClientFactory->create(
                $this->apiConfig->get($scopeType, $scopeId)
            );

            $result->setData($api->testAccess());
        } catch (\Exception $e) {
            $result->setData([
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }
}
