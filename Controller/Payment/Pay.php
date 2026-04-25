<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\PaymentException;
use PayPro\Przelewy24\Model\TransactionUrl;

class Pay implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \PayPro\Przelewy24\Model\RenewTransaction
     */
    private $renewTransaction;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \PayPro\Przelewy24\Model\RenewTransaction $renewTransaction,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->renewTransaction = $renewTransaction;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    public function execute(): ?ResultInterface
    {
        $result = $this->redirectFactory->create();

        $sessionId = $this->request->getParam('id');
        if (!$sessionId) {
            return $result->setPath('/');
        }

        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $this->renewTransaction->execute((string) $sessionId);
        } catch (PaymentException|LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $result->setPath('/');
        } catch (\Exception $e) {
            return $result->setPath('/');
        }

        $result->setUrl($payment->getAdditionalInformation(TransactionUrl::KEY));

        return $result;
    }
}
