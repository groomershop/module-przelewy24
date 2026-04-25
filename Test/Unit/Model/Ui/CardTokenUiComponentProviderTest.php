<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Ui;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use PayPro\Przelewy24\Model\Ui\CardTokenUiComponentProvider;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;

class CardTokenUiComponentProviderTest extends TestCase
{
    public function testGetComponentForToken(): void
    {
        $tokenUiComponentFactoryMock = $this->getMockBuilder(TokenUiComponentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenUiComponentMock = $this->getMockForAbstractClass(TokenUiComponentInterface::class);
        $paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $paymentTokenMock->expects($this->once())->method('getTokenDetails')->willReturn('{"a": 1}');
        $paymentTokenMock->expects($this->once())->method('getPublicHash')->willReturn('public_hash');
        $tokenUiComponentFactoryMock->expects($this->once())->method('create')->with([
            'config' => [
                'code' => ConfigProvider::CARD_VAULT_CODE,
                TokenUiComponentProviderInterface::COMPONENT_DETAILS => ['a' => 1],
                TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => 'public_hash',
            ],
            'name' => 'PayPro_Przelewy24/js/view/payment/method-renderer/przelewy24-card-vault',
        ])->willReturn($tokenUiComponentMock);

        $model = new CardTokenUiComponentProvider($tokenUiComponentFactoryMock, new Json());

        $this->assertEquals($tokenUiComponentMock, $model->getComponentForToken($paymentTokenMock));
    }
}
