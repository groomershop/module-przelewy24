<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Request;

use PayPro\Przelewy24\Gateway\Request\StoreIdRequestBuilder;
use PayPro\Przelewy24\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class StoreIdRequestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $subjectReaderMock->expects($this->once())->method('readOrderStoreId')->with(['data' => 1])->willReturn(1);

        $model = new StoreIdRequestBuilder($subjectReaderMock);

        $this->assertEquals([StoreIdRequestBuilder::STORE_ID => 1], $model->build(['data' => 1]));
    }
}
