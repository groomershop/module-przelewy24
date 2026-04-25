<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

class ApiAmount
{
    /**
     * @var float|null
     */
    private $amount;

    public function __construct(?float $amount)
    {
        $this->amount = $amount;
    }

    public function format(): int
    {
        if ($this->amount === null) {
            return 0;
        }

        return (int) round($this->amount * 100);
    }
}
