<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Status;

use Magento\Framework\Controller\ResultInterface;
use PayPro\Przelewy24\Api\WebhookHandlerInterface;
use PayPro\Przelewy24\Controller\Webhook;
use PayPro\Przelewy24\Model\Webhook\WebhookPayload;

class Blik extends Webhook
{
    public function execute(): ResultInterface
    {
        $result = $this->resultRawFactory->create();

        try {
            $payload = new WebhookPayload($this->request);

            $this->webhookHandler->handle($payload->get());
        } catch (\Exception $e) {
            $this->logger->error('Przelewy24 BLIK notification webhook: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $payload->getLog(),
            ]);

            return $result->setContents(WebhookHandlerInterface::FAILURE_RESPONSE);
        }

        return $result->setContents(WebhookHandlerInterface::SUCCESS_RESPONSE);
    }
}
