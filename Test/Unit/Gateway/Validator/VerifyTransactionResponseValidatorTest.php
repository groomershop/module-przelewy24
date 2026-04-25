<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Validator;

use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Gateway\Validator\VerifyTransactionResponseValidator;

class VerifyTransactionResponseValidatorTest extends ValidatorTestCase
{
    public function testValidate(): void
    {
        $validationSubject = ['data' => 1];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();

        $subjectReaderMock->expects($this->once())->method('readResponse')->with($validationSubject)->willReturn([
            'status' => 'success',
        ]);
        $this->resultFactoryMock->expects($this->once())->method('create')->with([
            'isValid' => true,
            'failsDescription' => [],
            'errorCodes' => [],
        ])->willReturn($this->resultMock);

        $model = new VerifyTransactionResponseValidator($this->resultFactoryMock, $subjectReaderMock);
        $this->assertSame($this->resultMock, $model->validate($validationSubject));
    }
}
