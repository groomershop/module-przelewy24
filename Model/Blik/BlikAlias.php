<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use PayPro\Przelewy24\Api\Data\BlikAliasInterface;

class BlikAlias implements BlikAliasInterface
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $label, string $value)
    {
        $this->label = $label;
        $this->value = $value;
    }

    public function getLabel(): string
    {
        return substr($this->label, 0, 35);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
