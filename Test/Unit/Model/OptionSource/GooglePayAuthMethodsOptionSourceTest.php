<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\OptionSource;

use PayPro\Przelewy24\Model\OptionSource\GooglePayAuthMethodsOptionSource;
use PHPUnit\Framework\TestCase;

class GooglePayAuthMethodsOptionSourceTest extends TestCase
{
    public function testToOptionArray(): void
    {
        $model = new GooglePayAuthMethodsOptionSource();
        $options = $model->toOptionArray();

        $this->assertEquals(count($options), count(array_column($options, 'value')));
        $this->assertEquals(count($options), count(array_column($options, 'label')));
    }
}
