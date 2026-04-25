<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Observer;

use PayPro\Przelewy24\Observer\TokenDataAssignObserver;

class TokenDataAssignObserverTest extends DataAssignObserverTestCase
{
    public function testExecute(): void
    {
        $observer = $this->prepareDataAssign([
            'token' => 'uuid',
            'sessionId' => 'uuid2',
        ]);

        $model = new TokenDataAssignObserver();
        $model->execute($observer);
    }

    public function testExecuteWithEmptyAdditionalData(): void
    {
        $observer = $this->prepareEmptyDataAssign();
        $model = new TokenDataAssignObserver();
        $model->execute($observer);
    }
}
