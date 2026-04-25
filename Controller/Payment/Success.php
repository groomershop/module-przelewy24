<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

class Success implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
    }

    public function execute(): ?ResultInterface
    {
        $result = $this->pageFactory->create();
        $result->getConfig()->getTitle()->set((string) __('Thank you for your payment'));

        return $result;
    }
}
