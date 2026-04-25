<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class LanguageRequestBuilder implements BuilderInterface
{
    private const LANGUAGE = 'language';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Model\LanguageResolver
     */
    private $languageResolver;

    public function __construct(
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Model\LanguageResolver $languageResolver
    ) {
        $this->subjectReader = $subjectReader;
        $this->languageResolver = $languageResolver;
    }

    public function build(array $buildSubject): array
    {
        $storeId = $this->subjectReader->readOrderStoreId($buildSubject);

        return [
            self::LANGUAGE => $this->languageResolver->resolve($storeId),
        ];
    }
}
