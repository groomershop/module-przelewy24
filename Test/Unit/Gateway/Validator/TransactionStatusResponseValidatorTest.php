<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Validator;

use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Gateway\Validator\TransactionStatusResponseValidator;
use PayPro\Przelewy24\Model\ErrorFormatter;

class TransactionStatusResponseValidatorTest extends ValidatorTestCase
{
    public function testValidate(): void
    {
        $validationSubject = ['data' => 1, 'payment' => $this->paymentDOMock];

        $subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();

        $subjectReaderMock->expects($this->once())->method('readResponse')->with($validationSubject)->willReturn([]);
        $this->resultFactoryMock->expects($this->once())->method('create')->with([
            'isValid' => false,
            'failsDescription' => [
                __(
                    'Przelewy24 error (%1: %2): %3',
                    $this->orderMock->getIncrementId(),
                    $this->paymentMock->getTransactionId(),
                    'transaction status not found',
                ),
            ],
            'errorCodes' => [],
        ])->willReturn($this->resultMock);

        $model = new TransactionStatusResponseValidator(
            $this->resultFactoryMock,
            $subjectReaderMock,
            new ErrorFormatter(new SubjectReader())
        );
        $this->assertSame($this->resultMock, $model->validate($validationSubject));
    }
}
