<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Model\Api;

use PayPro\Przelewy24\Model\Api\ApiPaymentMethod;
use PHPUnit\Framework\TestCase;

class ApiPaymentMethodTest extends TestCase
{
    public function testObject(): void
    {
        $model = new ApiPaymentMethod();
        $model->setId(1);
        $model->setName('payment');
        $model->setStatus(true);
        $model->setImgUrl('url1');
        $model->setMobileImgUrl('url2');
        $model->setIsStandalone(true);

        $this->assertEquals(1, $model->getId());
        $this->assertEquals('payment', $model->getName());
        $this->assertTrue($model->isActive());
        $this->assertEquals('url1', $model->getImgUrl());
        $this->assertEquals('url2', $model->getMobileImgUrl());
        $this->assertTrue($model->isStandalone());
    }
}
