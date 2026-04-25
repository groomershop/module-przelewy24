<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\DataObject;
use PayPro\Przelewy24\Api\Data\TransactionPayloadInterface;
use PayPro\Przelewy24\Model\Api\ApiAmount;
use PayPro\Przelewy24\Model\Api\ApiSignature;

class TransactionPayload extends DataObject implements TransactionPayloadInterface
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->setData(self::AMOUNT, new ApiAmount((float) $this->getData(self::AMOUNT)));

        $this->setData(self::SIGN, new ApiSignature([
            self::SESSION_ID => $this->getData(self::SESSION_ID),
            self::MERCHANT_ID => $this->getData(self::MERCHANT_ID),
            self::AMOUNT => $this->getAmount()->format(),
            self::CURRENCY => $this->getData(self::CURRENCY),
        ]));
    }

    public function getAmount(): ApiAmount
    {
        return $this->getData(self::AMOUNT);
    }

    public function getSignature(): ApiSignature
    {
        return $this->getData(self::SIGN);
    }

    public function get(string $crcKey): array
    {
        $data = $this->getData();
        $data[self::AMOUNT] = $this->getAmount()->format();
        $data[self::SIGN] = $this->getSignature()->sign($crcKey);

        return $data;
    }
}
