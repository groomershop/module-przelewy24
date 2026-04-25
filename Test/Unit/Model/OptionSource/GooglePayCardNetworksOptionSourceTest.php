<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\OptionSource;

use PayPro\Przelewy24\Model\OptionSource\GooglePayCardNetworksOptionSource;
use PHPUnit\Framework\TestCase;

class GooglePayCardNetworksOptionSourceTest extends TestCase
{
    public function testToOptionArray(): void
    {
        $model = new GooglePayCardNetworksOptionSource();
        $options = $model->toOptionArray();

        $this->assertEquals(count($options), count(array_column($options, 'value')));
        $this->assertEquals(count($options), count(array_column($options, 'label')));
    }
}
