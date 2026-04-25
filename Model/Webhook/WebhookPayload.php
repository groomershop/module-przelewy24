<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;

class WebhookPayload
{
    /**
     * @var string
     */
    private $requestContent;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$request instanceof \Magento\Framework\HTTP\PhpEnvironment\Request) {
            throw new LocalizedException(__('Webhook bad request'));
        }

        $this->requestContent = $request->getContent();
    }

    public function get(): array
    {
        $payload = json_decode($this->requestContent, true);
        if (!is_array($payload)) {
            throw new LocalizedException(__('Invalid payload'));
        }

        return $payload;
    }

    public function getLog(): array
    {
        try {
            return array_filter($this->get(), function ($k) {
                return $k !== 'sign';
            }, \ARRAY_FILTER_USE_KEY);
        } catch (LocalizedException $e) {
            return [];
        }
    }
}
