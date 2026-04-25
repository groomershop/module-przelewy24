<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class StoreIdRequestBuilder implements BuilderInterface
{
    const STORE_ID = 'store_id';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    public function build(array $buildSubject): array
    {
        return [
            self::STORE_ID => $this->subjectReader->readOrderStoreId($buildSubject),
        ];
    }
}
