<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Observer;

use PayPro\Przelewy24\Observer\CardDataAssignObserver;

class CardDataAssignObserverTest extends DataAssignObserverTestCase
{
    public function testExecute(): void
    {
        $observer = $this->prepareDataAssign([
            CardDataAssignObserver::REF_ID => 'ref_id',
            CardDataAssignObserver::CARD_TYPE => 'card_type',
            CardDataAssignObserver::CARD_MASK => 'card_mask',
            CardDataAssignObserver::CARD_DATE => 'card_date',
        ]);

        $model = new CardDataAssignObserver();
        $model->execute($observer);
    }

    public function testExecuteWithEmptyAdditionalData(): void
    {
        $observer = $this->prepareEmptyDataAssign();
        $model = new CardDataAssignObserver();
        $model->execute($observer);
    }
}
