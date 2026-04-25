<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentUpdateCommand extends Command
{
    const FORCE_OPTION = 'force';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \PayPro\Przelewy24\Gateway\Config\CommonConfig
     */
    private $config;

    /**
     * @var \PayPro\Przelewy24\Model\PaymentUpdate
     */
    private $paymentUpdate;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \PayPro\Przelewy24\Gateway\Config\CommonConfig $config,
        \PayPro\Przelewy24\Model\PaymentUpdate $paymentUpdate
    ) {
        parent::__construct('przelewy24:payment:update');
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->paymentUpdate = $paymentUpdate;
    }

    protected function configure(): void
    {
        $this->addOption(
            self::FORCE_OPTION,
            'f',
            InputOption::VALUE_NONE,
            'Get payment update for orders with Payment Review state'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errors = [];
        $skipped = 0;

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(OrderInterface::STATE, Order::STATE_PAYMENT_REVIEW)
            ->addFilter('payment_method_code', 'przelewy24%', 'like')
            ->addFilter(
                OrderInterface::CREATED_AT,
                (new \DateTime('now'))->sub(new \DateInterval('PT30M'))->format('Y-m-d H:i:s'),
                'lt'
            )->create();

        $result = $this->orderRepository->getList($searchCriteria);

        $progressBar = new ProgressBar($output, $result->getTotalCount());
        $progressBar->display();

        /** @var bool $forceFlag */
        $forceFlag = $input->getOption(self::FORCE_OPTION);

        foreach ($result->getItems() as $order) {
            if (!$this->config->isPaymentAutoUpdateEnabled((int) $order->getStoreId()) && !$forceFlag) {
                $progressBar->advance();
                $skipped++;
                continue;
            }

            try {
                $this->paymentUpdate->execute((int) $order->getEntityId());
                $progressBar->advance();
            } catch (\Exception $e) {
                $errors[$order->getIncrementId()] = $e->getMessage();
            }
        }

        $progressBar->finish();
        $output->writeln('');

        if ($skipped > 0) {
            $output->writeln(
                sprintf('<info>%d payment(s) skipped</info>', $skipped)
            );
        }

        if (!empty($errors)) {
            foreach ($errors as $orderId => $error) {
                $output->writeln(sprintf('<error>#%s: %s</error>', $orderId, $error));
            }

            $output->writeln(
                sprintf('<info>%d payment(s) updated</info>', $result->getTotalCount() - $skipped - count($errors))
            );

            return Cli::RETURN_FAILURE;
        }

        $output->writeln(sprintf('<info>%d payment(s) updated</info>', $result->getTotalCount() - $skipped));

        return Cli::RETURN_SUCCESS;
    }
}
