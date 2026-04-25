<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

interface WebhookHandlerInterface
{
    const SUCCESS_RESPONSE = 'OK';
    const FAILURE_RESPONSE = 'ERROR';

    /**
     * @param array $payload
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $payload): void;
}
