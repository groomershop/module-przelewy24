<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Observer;

use PayPro\Przelewy24\Observer\BlikDataAssignObserver;

class BlikDataAssignObserverTest extends DataAssignObserverTestCase
{
    public function testExecute(): void
    {
        $observer = $this->prepareDataAssign([
            'blikCode' => 123456,
        ]);

        $model = new BlikDataAssignObserver();
        $model->execute($observer);
    }

    public function testExecuteWithEmptyAdditionalData(): void
    {
        $observer = $this->prepareEmptyDataAssign();
        $model = new BlikDataAssignObserver();
        $model->execute($observer);
    }
}
