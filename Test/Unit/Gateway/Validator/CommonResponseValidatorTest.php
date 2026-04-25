<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Validator;

use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Gateway\Validator\CommonResponseValidator;
use PayPro\Przelewy24\Model\ErrorFormatter;

class CommonResponseValidatorTest extends ValidatorTestCase
{
    public function testValidate(): void
    {
        $validationSubject = ['data' => 1, 'payment' => $this->paymentDOMock];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->resultFactoryMock->expects($this->once())->method('create')->with([
            'isValid' => false,
            'failsDescription' => [
                __(
                    'Przelewy24 error (%1: %2): %3',
                    $this->orderMock->getIncrementId(),
                    $this->paymentMock->getTransactionId(),
                    'error',
                ),
            ],
            'errorCodes' => [],
        ])->willReturn($this->resultMock);

        $subjectReaderMock->expects($this->once())
            ->method('readResponseError')
            ->with($validationSubject)
            ->willReturn('error');

        $model = new CommonResponseValidator(
            $this->resultFactoryMock,
            $subjectReaderMock,
            new ErrorFormatter(new SubjectReader())
        );

        $this->assertSame($this->resultMock, $model->validate($validationSubject));
    }
}
