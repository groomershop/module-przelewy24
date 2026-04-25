<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use Magento\Sales\Api\Data\OrderItemInterface;
use PayPro\Przelewy24\Model\Api\ApiCart;
use PHPUnit\Framework\TestCase;

class ApiCartTest extends TestCase
{
    private const SELLER_ID = '1';
    private const SELLER_CATEGORY = 'seller category';

    public function testToArray(): void
    {
        $cartItemNoQuantityMock = $this->getMockForAbstractClass(OrderItemInterface::class);
        $cartItemNoQuantityMock->expects($this->once())->method('getQtyOrdered')->willReturn(1);
        $cartItemNoQuantityMock->expects($this->once())->method('getQtyInvoiced')->willReturn(1);
        $cartItemNoQuantityMock->expects($this->never())->method('getPriceInclTax');

        $cartItemMock = $this->getMockForAbstractClass(OrderItemInterface::class);
        $cartItemMock->expects($this->once())->method('getQtyOrdered')->willReturn(1);
        $cartItemMock->expects($this->once())->method('getQtyInvoiced')->willReturn(0);
        $cartItemMock->expects($this->once())->method('getRowTotalInclTax')->willReturn('10.5000');
        $cartItemMock->expects($this->once())->method('getName')->willReturn('Product');
        $cartItemMock->expects($this->once())->method('getDescription')->willReturn('Product description');
        $cartItemMock->expects($this->once())->method('getSku')->willReturn('Product SKU');

        $cartItems = [
            $cartItemNoQuantityMock,
            $cartItemMock,
        ];

        $this->assertEquals([
            [
                'sellerId' => self::SELLER_ID,
                'sellerCategory' => self::SELLER_CATEGORY,
                'name' => 'Product',
                'description' => 'Product description',
                'quantity' => 1,
                'price' => 1050,
                'number' => 'Product SKU',
            ],
        ], (new ApiCart(self::SELLER_ID, self::SELLER_CATEGORY, $cartItems))->toArray());
    }
}
