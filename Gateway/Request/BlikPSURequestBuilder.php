<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class BlikPSURequestBuilder implements BuilderInterface
{
    private const ADDITIONAL = 'additional';
    private const PSU = 'PSU';
    private const IP = 'IP';
    private const USER_AGENT = 'userAgent';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private \Magento\Framework\App\Request\Http $request;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private \Magento\Framework\HTTP\Header $httpHeader;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        $this->request = $request;
        $this->httpHeader = $httpHeader;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        return [
            self::ADDITIONAL => [
                self::PSU => [
                    self::IP => $this->getIp(),
                    self::USER_AGENT => $this->httpHeader->getHttpUserAgent(),
                ],
            ],
        ];
    }

    private function getIp(): string
    {
        $ips = array_filter(explode(',', (string) $this->request->getClientIp()));

        return (string) reset($ips);
    }
}
