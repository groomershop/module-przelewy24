<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Http;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Store\Model\ScopeInterface;
use PayPro\Przelewy24\Gateway\Request\StoreIdRequestBuilder;
use PayPro\Przelewy24\Model\Api\ApiConfig;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var \PayPro\Przelewy24\Model\Api\ApiConfig
     */
    private $apiConfig;

    public function __construct(
        \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder,
        \PayPro\Przelewy24\Model\Api\ApiConfig $apiConfig
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->apiConfig = $apiConfig;
    }

    public function create(array $request): TransferInterface
    {
        if (!isset($request[StoreIdRequestBuilder::STORE_ID])) {
            throw new CommandException(__('Invalid request: missing store id'));
        }

        $storeId = $request[StoreIdRequestBuilder::STORE_ID];
        unset($request[StoreIdRequestBuilder::STORE_ID]);

        [
            ApiConfig::URL => $url,
            ApiConfig::USERNAME => $username,
            ApiConfig::PASSWORD => $password,
        ] = $this->apiConfig->get(ScopeInterface::SCOPE_STORE, $storeId);

        return $this->transferBuilder
            ->setUri($url)
            ->setAuthUsername($username)
            ->setAuthPassword($password)
            ->setBody($request)
            ->build();
    }
}
