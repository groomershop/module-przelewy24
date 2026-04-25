<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\GooglePay;

use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Gateway\Config\GooglePayConfig;
use PayPro\Przelewy24\Model\GooglePay\RegisterGooglePayTransaction;
use PayPro\Przelewy24\Model\RegisterTransaction;
use PayPro\Przelewy24\Test\Unit\Model\RegisterBundlePayTransactionTestCase;

class RegisterGooglePayTransactionTest extends RegisterBundlePayTransactionTestCase
{
    const METHOD_ID = 265;

    public function testExecute(): void
    {
        $configMock = $this->createMock(GooglePayConfig::class);
        $configMock->expects($this->once())->method('getMethodId')->willReturn(self::METHOD_ID);
        $model = new RegisterGooglePayTransaction(
            $configMock,
            $this->registerTransactionMock,
            $this->tokenTransactionFactoryMock
        );

        $this->registerTransactionMock->expects($this->once())->method('execute')->with(
            '1',
            [
                TransactionPayloadInterface::CARD_DATA => [
                    TransactionPayloadInterface::MEANS => [
                        TransactionPayloadInterface::X_PAY_PAYLOAD => [
                            TransactionPayloadInterface::PAYLOAD => base64_encode(self::BUNDLE_PAY_TOKEN),
                            TransactionPayloadInterface::TYPE => RegisterGooglePayTransaction::TYPE,
                        ],
                    ],
                ],
                TransactionPayloadInterface::METHOD => self::METHOD_ID,
            ]
        )->willReturn([
            RegisterTransaction::RESPONSE => ['token' => self::TOKEN],
            RegisterTransaction::PAYLOAD => [TransactionPayloadInterface::SESSION_ID => self::SESSION_ID],
        ]);

        $this->assertEquals($this->tokenTransactionMock, $model->execute(self::CART_ID, self::BUNDLE_PAY_TOKEN));
    }
}
