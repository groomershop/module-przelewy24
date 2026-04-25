<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Observer;

use PayPro\Przelewy24\Observer\GatewayDataAssignObserver;

class GatewayDataAssignObserverTest extends DataAssignObserverTestCase
{
    public function testExecute(): void
    {
        $observer = $this->prepareDataAssign([
            'method' => '181',
            'regulation_accept' => true,
        ]);

        $model = new GatewayDataAssignObserver();
        $model->execute($observer);
    }

    public function testExecuteWithEmptyAdditionalData(): void
    {
        $observer = $this->prepareEmptyDataAssign();
        $model = new GatewayDataAssignObserver();
        $model->execute($observer);
    }
}
