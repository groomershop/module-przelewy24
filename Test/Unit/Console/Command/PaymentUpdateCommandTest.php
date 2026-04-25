<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Test\Unit\Console\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PayPro\Przelewy24\Console\Command\PaymentUpdateCommand;
use PayPro\Przelewy24\Gateway\Config\CommonConfig;
use PayPro\Przelewy24\Model\PaymentUpdate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PaymentUpdateCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $configMock = $this->createMock(CommonConfig::class);
        $configMock->expects($this->exactly(3))->method('isPaymentAutoUpdateEnabled')->willReturnOnConsecutiveCalls(
            true,
            false,
            true
        );

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->atLeast(1))->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $orders = [
            $this->createMock(OrderInterface::class),
            $this->createMock(OrderInterface::class),
            $this->createMock(OrderInterface::class),
        ];

        $orders[0]->expects($this->once())->method('getIncrementId')->willReturn('000000001');

        $searchResultMock = $this->createMock(OrderSearchResultInterface::class);
        $searchResultMock->expects($this->once())->method('getItems')->willReturn($orders);
        $searchResultMock->expects($this->any())->method('getTotalCount')->willReturn(3);

        $orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $orderRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $paymentUpdateMock = $this->createMock(PaymentUpdate::class);
        $paymentUpdateMatcher = $this->exactly(2);
        $paymentUpdateMock->expects($paymentUpdateMatcher)->method('execute')
            ->willReturnCallback(function () use ($paymentUpdateMatcher) {
                if ($paymentUpdateMatcher->getInvocationCount() === 1) {
                    throw new \Exception('Error');
                }
            });

        $command = new PaymentUpdateCommand(
            $searchCriteriaBuilderMock,
            $orderRepositoryMock,
            $configMock,
            $paymentUpdateMock
        );

        $commandTester = new CommandTester($command);

        $commandTester->execute([], ['interactive' => false]);

        $this->assertStringContainsString(
            '1 payment(s) skipped',
            $commandTester->getDisplay()
        );

        $this->assertStringContainsString(
            '#000000001: Error',
            $commandTester->getDisplay()
        );

        $this->assertStringContainsString(
            '1 payment(s) updated',
            $commandTester->getDisplay()
        );
    }
}
