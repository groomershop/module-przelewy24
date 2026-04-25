<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Blik;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PayPro\Przelewy24\Api\Data\BlikResponseInterface;
use PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Gateway\Request\BlikPSURequestBuilder;
use PayPro\Przelewy24\Model\Blik\ChargeBlik;
use PayPro\Przelewy24\Model\Blik\RegisterBlikTransaction;
use PayPro\Przelewy24\Model\RegisterTransaction;
use PHPUnit\Framework\TestCase;

class RegisterBlikTransactionTest extends TestCase
{
    public function testExecute(): void
    {
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->once())->method('getUrl')->willReturn('notification_url');
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $cartRepositoryMock->expects($this->once())->method('getActive')->with(1)->willReturn($quoteMock);
        $maskedQuoteIdToQuoteIdMock = $this->createMock(MaskedQuoteIdToQuoteIdInterface::class);
        $registerTransactionMock = $this->createMock(RegisterTransaction::class);

        $registerTransactionMock->expects($this->once())->method('execute')->with(
            '1',
            [
                TransactionPayloadInterface::REFERENCE_REGISTER => true,
                TransactionPayloadInterface::URL_CARD_PAYMENT_NOTIFICATION => 'notification_url',
            ]
        )->willReturn([
            RegisterTransaction::RESPONSE => ['token' => 'uuid'],
            RegisterTransaction::PAYLOAD => ['sessionId' => 'uuid2'],
        ]);

        $blikResponseMock = $this->createMock(BlikResponseInterface::class);
        $blikResponseFactoryMock = $this->createMock(BlikResponseInterfaceFactory::class);
        $blikResponseFactoryMock->expects($this->once())->method('create')->with([
            BlikResponseInterface::SUCCESS => true,
            BlikResponseInterface::MESSAGE => 'Confirm payment in banking application.',
            BlikResponseInterface::SESSION_ID => 'uuid2',
        ])->willReturn($blikResponseMock);

        $blikPsuRequestBuilderMock = $this->createMock(BlikPSURequestBuilder::class);
        $chargeBlikMock = $this->createMock(ChargeBlik::class);

        $model = new RegisterBlikTransaction(
            $cartRepositoryMock,
            $maskedQuoteIdToQuoteIdMock,
            $registerTransactionMock,
            $blikResponseFactoryMock,
            $blikPsuRequestBuilderMock,
            $chargeBlikMock
        );
        $this->assertEquals($blikResponseMock, $model->execute('1', '777777', true));
    }
}
