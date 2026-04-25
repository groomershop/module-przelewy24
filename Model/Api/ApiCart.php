<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

class ApiCart
{
    private const SELLER_ID = 'sellerId';
    private const SELLER_CATEGORY = 'sellerCategory';
    private const NAME = 'name';
    private const DESCRIPTION = 'description';
    private const QUANTITY = 'quantity';
    private const PRICE = 'price';
    private const NUMBER = 'number';

    /**
     * @var string
     */
    private $sellerId;

    /**
     * @var string
     */
    private $sellerCategory;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    private $cartItems;

    /**
     * @param string $sellerId
     * @param string $sellerCategory
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $cartItems
     */
    public function __construct(string $sellerId, string $sellerCategory, array $cartItems)
    {
        $this->sellerId = $sellerId;
        $this->sellerCategory = $sellerCategory;
        $this->cartItems = $cartItems;
    }

    public function toArray(): array
    {
        $cart = [];

        foreach ($this->cartItems as $item) {
            $qty = (int) ($item->getQtyOrdered() - $item->getQtyInvoiced());
            if ($qty <= 0) {
                continue;
            }

            $itemPrice = ((float) $item->getRowTotalInclTax() - (float) $item->getDiscountAmount()) / $qty;

            $price = new ApiAmount($itemPrice);

            $cart[] = [
                self::SELLER_ID => $this->sellerId,
                self::SELLER_CATEGORY => $this->sellerCategory,
                self::NAME => $item->getName(),
                self::DESCRIPTION => $item->getDescription(),
                self::QUANTITY => $qty,
                self::PRICE => $price->format(),
                self::NUMBER => $item->getSku(),
            ];
        }

        return $cart;
    }
}
