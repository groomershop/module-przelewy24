<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Card;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;
use PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory;
use PayPro\Przelewy24\Gateway\Config\CardConfig;
use PayPro\Przelewy24\Model\Card\RegisterCardTransaction;
use PayPro\Przelewy24\Model\RegisterTransaction;
use PHPUnit\Framework\TestCase;

class RegisterCardTransactionTest extends TestCase
{
    const CART_ID = '1';
    const SESSION_ID = 'session-uuid';
    const REF_ID = 'ref-uuid';
    const TOKEN = 'token-uuid';

    public function testExecute(): void
    {
        $configMock = $this->createMock(CardConfig::class);
        $registerTransactionMock = $this->getMockBuilder(RegisterTransaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registerTransactionMock->expects($this->once())->method('execute')->willReturn([
            RegisterTransaction::RESPONSE => ['token' => self::TOKEN],
        ]);

        $tokenTransactionMock = $this->getMockForAbstractClass(TokenTransactionInterface::class);
        $tokenTransactionFactoryMock = $this->getMockBuilder(TokenTransactionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenTransactionFactoryMock->expects($this->once())->method('create')->with([
            TokenTransactionInterface::TOKEN => self::TOKEN,
            TokenTransactionInterface::TRANSACTION => self::SESSION_ID,
        ])->willReturn($tokenTransactionMock);

        $model = new RegisterCardTransaction(
            $configMock,
            $registerTransactionMock,
            $tokenTransactionFactoryMock
        );

        $this->assertEquals($tokenTransactionMock, $model->execute(self::CART_ID, self::SESSION_ID, self::REF_ID));
    }
}
