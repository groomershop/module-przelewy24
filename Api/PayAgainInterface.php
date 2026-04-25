<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use Magento\Sales\Model\Order\Payment;

interface PayAgainInterface
{
    public function execute(string $sessionId, Payment $payment, array $additionalData): void;
}
