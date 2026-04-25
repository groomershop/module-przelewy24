<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model;

use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Model\TransactionUrl;
use PHPUnit\Framework\TestCase;

class TransactionUrlTest extends TestCase
{
    public function testGetTransactionUrl(): void
    {
        $configMock = $this->getMockBuilder(CommonConfig::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('getGatewayUrl')->willReturn('https://sandbox.przelewy24.pl/');
        $model = new TransactionUrl($configMock);
        $this->assertEquals('https://sandbox.przelewy24.pl/trnRequest/TOKEN', $model->get('TOKEN'));
    }
}
