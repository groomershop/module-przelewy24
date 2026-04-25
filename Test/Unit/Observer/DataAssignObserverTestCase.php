<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use PHPUnit\Framework\TestCase;

class DataAssignObserverTestCase extends TestCase
{
    protected function prepareDataAssign(array $observerData): Observer
    {
        $data = new DataObject([
            'additional_data' => $observerData,
        ]);

        $observerMock = $this->getMockBuilder(Observer::class)->disableOriginalConstructor()->getMock();
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        $paymentModelMock = $this->getMockForAbstractClass(InfoInterface::class);
        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->exactly(2))
            ->method('getDataByKey')
            ->withConsecutive(['data'], ['payment_model'])
            ->willReturnOnConsecutiveCalls($data, $paymentModelMock);

        $additionalInformationPayload = array_map(function ($key, $value) {
            return [$key, $value];
        }, array_keys($observerData), $observerData);

        $paymentModelMock->expects($this->exactly(count($observerData)))
            ->method('setAdditionalInformation')
            ->withConsecutive(...$additionalInformationPayload);

        return $observerMock;
    }

    protected function prepareEmptyDataAssign(): Observer
    {
        $data = new DataObject();
        $observerMock = $this->getMockBuilder(Observer::class)->disableOriginalConstructor()->getMock();
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('getDataByKey')
            ->with('data')
            ->willReturn($data);

        return $observerMock;
    }
}
