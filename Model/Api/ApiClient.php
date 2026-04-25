<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use PayPro\Przelewy24\Api\ApiClientInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ApiClient implements ApiClientInterface
{
    /**
     * @var \GuzzleHttp\ClientFactory
     */
    private $clientFactory;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $url;

    public function __construct(
        \GuzzleHttp\ClientFactory $clientFactory,
        string $url,
        string $username,
        string $password
    ) {
        $this->clientFactory = $clientFactory;
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }

    public function testAccess(): array
    {
        return $this->request('GET', self::TEST_ACCESS_ENDPOINT);
    }

    public function paymentMethods(string $lang = 'en'): array
    {
        $response = $this->request('GET', sprintf(self::PAYMENT_METHODS_ENDPOINT, $lang));

        return $response['data'] ?? [];
    }

    public function registerTransaction(array $params): array
    {
        return $this->request('POST', self::REGISTER_TRANSACTION_ENDPOINT, [RequestOptions::FORM_PARAMS => $params]);
    }

    public function verifyTransaction(array $params): array
    {
        return $this->request('PUT', self::VERIFY_TRANSACTION_ENDPOINT, [RequestOptions::FORM_PARAMS => $params]);
    }

    public function refundTransaction(array $params): array
    {
        return $this->request('POST', self::REFUND_TRANSACTION_ENDPOINT, [RequestOptions::FORM_PARAMS => $params]);
    }

    public function refundInfo(int $orderId): array
    {
        return $this->request('GET', sprintf(self::REFUND_INFO_ENDPOINT, $orderId));
    }

    public function transactionStatus(string $sessionId): array
    {
        return $this->request('GET', sprintf(self::TRANSACTION_STATUS_ENDPOINT, $sessionId));
    }

    public function cardInfo(int $orderId): array
    {
        return $this->request('GET', sprintf(self::CARD_INFO_ENDPOINT, $orderId));
    }

    public function chargeCard(string $token): array
    {
        return $this->request('POST', self::CHARGE_CARD_ENDPOINT, [RequestOptions::FORM_PARAMS => ['token' => $token]]);
    }

    public function blikChargeByCode(array $params): array
    {
        return $this->request('POST', self::BLIK_CHARGE_BY_CODE_ENDPOINT, [RequestOptions::FORM_PARAMS => $params]);
    }

    public function blikChargeByAlias(array $params): array
    {
        return $this->request('POST', self::BLIK_CHARGE_BY_ALIAS_ENDPOINT, [RequestOptions::FORM_PARAMS => $params]);
    }

    private function client(): \GuzzleHttp\ClientInterface
    {
        if ($this->client === null) {
            $this->client = $this->clientFactory->create([
                'config' => [
                    'base_uri' => $this->url,
                    'auth' => [$this->username, $this->password],
                ],
            ]);
        }

        return $this->client;
    }

    private function request(string $method, string $url, array $params = []): array
    {
        try {
            $responseBody = $this->client()->request($method, $url, $params)->getBody();
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody();
        }

        $response = json_decode(
            (string) $responseBody,
            true
        );

        return is_array($response) ? $response : [];
    }
}
