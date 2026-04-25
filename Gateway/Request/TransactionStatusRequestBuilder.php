<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPro\Przelewy24\Model\Api\ApiTransaction;

class TransactionStatusRequestBuilder implements BuilderInterface
{
    private const TRANSACTION_ID = 'transactionId';

    public function build(array $buildSubject): array
    {
        return [
            ApiTransaction::SESSION_ID => (string) $buildSubject[self::TRANSACTION_ID],
        ];
    }
}
