<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Phrase;

class ErrorFormatter
{
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    public function format(string $error, array $subject): Phrase
    {
        $orderIncrementId = $this->subjectReader->readOrderIncrementId($subject);
        $transactionId = $subject['transactionId'] ?? $this->subjectReader->readTransactionId($subject);

        return __('Przelewy24 error (%1: %2): %3', $orderIncrementId, $transactionId, $error);
    }
}
