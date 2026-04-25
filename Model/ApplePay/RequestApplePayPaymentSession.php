<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\ApplePay;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PayPro\Przelewy24\Gateway\Config\ApplePayConfig;

class RequestApplePayPaymentSession
{
    /**
     * @var \GuzzleHttp\ClientFactory
     */
    private $clientFactory;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $paymentLogger;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\ApplePayConfig
     */
    private $applePayConfig;

    public function __construct(
        \GuzzleHttp\ClientFactory $clientFactory,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \PayPro\Przelewy24\Gateway\Config\ApplePayConfig $applePayConfig
    ) {
        $this->clientFactory = $clientFactory;
        $this->paymentLogger = $paymentLogger;
        $this->applePayConfig = $applePayConfig;
    }

    public function execute(string $validationUrl): array
    {
        $certificate = $this->applePayConfig->getCertificateFilePath();
        $sslKey = $this->applePayConfig->getSSLKeyFilePath();

        if (!$certificate || !$sslKey) {
            $this->paymentLogger->debug([
                'applePaySessionRequest' => 'Missing SSL files',
                'certificate' => $certificate,
                'key' => $sslKey,
            ]);

            return [];
        }

        $client = $this->clientFactory->create();

        try {
            $responseBody = $client->request('POST', $validationUrl, [
                RequestOptions::JSON => [
                    'merchantIdentifier' => $this->applePayConfig->getMerchantIdentifier(),
                    'displayName' => $this->applePayConfig->getDisplayName(),
                    'initiative' => ApplePayConfig::INITIATIVE,
                    'initiativeContext' => $this->applePayConfig->getInitiativeContext(),
                ],
                RequestOptions::CERT => $certificate,
                RequestOptions::SSL_KEY => $sslKey,
            ])->getBody();
        } catch (RequestException $e) {
            $this->paymentLogger->debug([
                'request' => (string) $e->getRequest()->getBody(),
                'applePaySessionResponse' => $e->getResponse() === null
                    ? $e->getMessage()
                    : (string) $e->getResponse()->getBody(),
            ]);

            return [];
        }

        $response = json_decode((string) $responseBody, true);

        return is_array($response) ? $response : [];
    }
}
