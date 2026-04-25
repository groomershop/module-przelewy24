<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Gateway\Validator;

use PayPro\Przelewy24\Gateway\Config\GatewayConfig;
use PayPro\Przelewy24\Gateway\Validator\CurrencyValidator;

class CurrencyValidatorTest extends ValidatorTestCase
{
    public function testValidate(): void
    {
        $validationSubject = [
            'storeId' => '1',
            'currency' => 'PLN',
        ];

        $configMock = $this->getMockBuilder(GatewayConfig::class)->disableOriginalConstructor()->getMock();

        $configMock->expects($this->once())->method('isCurrencyAllowed')->willReturn(true);
        $this->resultFactoryMock->expects($this->once())->method('create')->with([
            'isValid' => true,
            'failsDescription' => [],
            'errorCodes' => [],
        ])->willReturn($this->resultMock);

        $model = new CurrencyValidator($this->resultFactoryMock, $configMock);

        $this->assertSame($this->resultMock, $model->validate($validationSubject));
    }
}
