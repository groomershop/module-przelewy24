<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Blik;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\PaymentException;
use PayPro\Przelewy24\Api\Data\BlikResponseInterface;

class PayBlik implements \PayPro\Przelewy24\Api\PayBlikInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\RenewTransaction
     */
    private \PayPro\Przelewy24\Model\RenewTransaction $renewTransaction;

    /**
     * @var \PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory
     */
    private \PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory $blikResponseFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \PayPro\Przelewy24\Model\RenewTransaction $renewTransaction,
        \PayPro\Przelewy24\Api\Data\BlikResponseInterfaceFactory $blikResponseFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->renewTransaction = $renewTransaction;
        $this->logger = $logger;
        $this->blikResponseFactory = $blikResponseFactory;
    }

    public function execute(string $sessionId, string $blikCode): \PayPro\Przelewy24\Api\Data\BlikResponseInterface
    {
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $this->renewTransaction->execute($sessionId, [ChargeBlik::BLIK_CODE => $blikCode]);

            $response = $payment->getBlikResponse();
        } catch (PaymentException|LocalizedException $e) {
            $response = [
                BlikResponseInterface::SUCCESS => false,
                BlikResponseInterface::MESSAGE => $e->getMessage(),
                BlikResponseInterface::SESSION_ID => '',
            ];
        } catch (\Exception $e) {
            $this->logger->error('BLIK pay again error', ['exception' => $e]);
            $response = [
                BlikResponseInterface::SUCCESS => false,
                BlikResponseInterface::MESSAGE => (string) __('Transaction has been declined. Please try again later.'),
                BlikResponseInterface::SESSION_ID => '',
            ];
        }

        return $this->blikResponseFactory->create($response);
    }
}
