<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use PayPro\Przelewy24\Api\Data\TokenTransactionInterface;
use PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory;
use PayPro\Przelewy24\Model\RegisterTransaction;
use PHPUnit\Framework\TestCase;

class RegisterBundlePayTransactionTestCase extends TestCase
{
    const CART_ID = '1';
    const SESSION_ID = 'session-uuid';
    const TOKEN = 'token-uuid';
    const BUNDLE_PAY_TOKEN = 'bundle-pay-token';

    /**
     * @var \PayPro\Przelewy24\Model\RegisterTransaction|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registerTransactionMock;

    /**
     * @var \PayPro\Przelewy24\Api\Data\TokenTransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokenTransactionMock;

    /**
     * @var \PayPro\Przelewy24\Api\Data\TokenTransactionInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokenTransactionFactoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerTransactionMock = $this->getMockBuilder(RegisterTransaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenTransactionMock = $this->getMockForAbstractClass(TokenTransactionInterface::class);
        $this->tokenTransactionFactoryMock = $this->getMockBuilder(TokenTransactionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenTransactionFactoryMock->expects($this->once())->method('create')->with([
            TokenTransactionInterface::TOKEN => self::TOKEN,
            TokenTransactionInterface::TRANSACTION => self::SESSION_ID,
        ])->willReturn($this->tokenTransactionMock);
    }
}
