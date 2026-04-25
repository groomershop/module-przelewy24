<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Blik;

use PayPro\Przelewy24\Model\Blik\BlikAlias;
use PHPUnit\Framework\TestCase;

class BlikAliasTest extends TestCase
{
    public function testAlias(): void
    {
        $alias = new BlikAlias('098f6bcd4621d373cade4e832627b4f61234', 'value');

        $this->assertEquals('value', $alias->getValue());
        $this->assertEquals('098f6bcd4621d373cade4e832627b4f6123', $alias->getLabel());
    }
}
