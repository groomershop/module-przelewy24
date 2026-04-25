<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Payment;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class ApplePaySession implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestContentInterface
     */
    private $request;

    /**
     * @var \PayPro\Przelewy24\Model\ApplePay\RequestApplePayPaymentSession
     */
    private $requestApplePayPaymentSession;

    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\RequestContentInterface $request,
        \PayPro\Przelewy24\Model\ApplePay\RequestApplePayPaymentSession $requestApplePayPaymentSession
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->requestApplePayPaymentSession = $requestApplePayPaymentSession;
    }

    public function execute(): ?ResultInterface
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        ['validationUrl' => $validationUrl] = json_decode($this->request->getContent(), true);

        $applePayPaymentSessionObject = $this->requestApplePayPaymentSession->execute($validationUrl);
        if (empty($applePayPaymentSessionObject)) {
            $result->setHttpResponseCode(500);
        }

        return $result->setData($applePayPaymentSessionObject);
    }
}
