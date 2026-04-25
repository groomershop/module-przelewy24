<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PayPro\Przelewy24\Cron\PaymentUpdateCron;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Model\PaymentUpdate;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PaymentUpdateCronTest extends TestCase
{
    public function testExecute(): void
    {
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->atLeast(1))->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $searchResultMock = $this->createMock(OrderSearchResultInterface::class);

        $orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $orderRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())->method('getItems')->willReturn([
            $this->createOrderMock(),
            $this->createOrderMock(),
            $this->createOrderMock(),
        ]);

        $paymentUpdateMock = $this->createMock(PaymentUpdate::class);
        $configMock = $this->createMock(CommonConfig::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $configMock->expects($this->exactly(3))->method('isPaymentAutoUpdateEnabled')->willReturnOnConsecutiveCalls(
            true,
            false,
            true
        );

        $paymentUpdateMatcher = $this->exactly(2);
        $paymentUpdateMock->expects($paymentUpdateMatcher)->method('execute')
            ->willReturnCallback(function () use ($paymentUpdateMatcher) {
                if ($paymentUpdateMatcher->getInvocationCount() === 1) {
                    throw new \Exception('Error');
                }
            });

        $loggerMock->expects($this->once())->method('error');

        $model = new PaymentUpdateCron(
            $searchCriteriaBuilderMock,
            $orderRepositoryMock,
            $configMock,
            $paymentUpdateMock,
            $loggerMock
        );

        $model->execute();
    }

    private function createOrderMock(): OrderInterface
    {
        return $this->createMock(OrderInterface::class);
    }
}
