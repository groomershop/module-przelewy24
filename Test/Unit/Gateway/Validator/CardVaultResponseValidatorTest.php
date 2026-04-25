<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Validator;

use PayPro\Przelewy24\Gateway\SubjectReader;
use PayPro\Przelewy24\Gateway\Validator\CardVaultResponseValidator;
use PayPro\Przelewy24\Model\ErrorFormatter;

class CardVaultResponseValidatorTest extends ValidatorTestCase
{
    public function testValidate(): void
    {
        $validationSubject = ['response' => ['data' => []], 'payment' => $this->paymentDOMock];
        $this->resultFactoryMock->expects($this->once())->method('create')->with([
            'isValid' => false,
            'failsDescription' => [
                __(
                    'Przelewy24 error (%1: %2): %3',
                    $this->orderMock->getIncrementId(),
                    $this->paymentMock->getTransactionId(),
                    '3DS redirect url not found',
                ),
            ],
            'errorCodes' => [],
        ])->willReturn($this->resultMock);

        $subjectReader = new SubjectReader();
        $model = new CardVaultResponseValidator(
            $this->resultFactoryMock,
            $subjectReader,
            new ErrorFormatter($subjectReader)
        );
        $this->assertSame($this->resultMock, $model->validate($validationSubject));
    }
}
