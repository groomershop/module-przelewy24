<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use PayPro\Przelewy24\Gateway\Request\LanguageRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Model\LanguageResolver;
use PHPUnit\Framework\TestCase;

class LanguageRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $buildSubject = ['data' => 1];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $languageResolverMock = $this->getMockBuilder(LanguageResolver::class)->disableOriginalConstructor()->getMock();
        $subjectReaderMock->expects($this->once())->method('readOrderStoreId')->willReturn(1);
        $languageResolverMock->expects($this->once())->method('resolve')->with(1)->willReturn('en');

        $model = new LanguageRequestBuilder($subjectReaderMock, $languageResolverMock);

        $this->assertEquals(['language' => 'en'], $model->build($buildSubject));
    }
}
