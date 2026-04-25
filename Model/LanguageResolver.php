<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

class LanguageResolver
{
    const DEFAULT_LANGUAGE = 'en';

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    public function resolve(?int $storeId = null): string
    {
        $locale = $storeId === null ? $this->localeResolver->getLocale() : $this->localeResolver->emulate($storeId);
        if ($locale === null) {
            return self::DEFAULT_LANGUAGE;
        }

        $language = strstr($locale, '_', true);

        return $language ?: self::DEFAULT_LANGUAGE;
    }
}
