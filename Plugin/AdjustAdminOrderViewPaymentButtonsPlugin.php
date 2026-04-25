<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;
use PayPro\Przelewy24\Model\Ui\ConfigProvider;

class AdjustAdminOrderViewPaymentButtonsPlugin
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    public function __construct(
        \Magento\Framework\UrlInterface $url
    ) {
        $this->url = $url;
    }

    public function beforeSetLayout(View $subject): void
    {
        $order = $subject->getOrder();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        if (strpos($payment->getMethod(), ConfigProvider::CODE) === false) {
            return;
        }

        $subject->addButton('deny_payment', [
            'label' => __('Deny Payment'),
            'onclick' => 'confirmSetLocation("'
                . __('Are you sure you want to deny this payment?')
                . '", "' . $subject->getReviewPaymentUrl('deny') . '")',
        ]);

        $subject->addButton('get_review_payment_update', [
            'label' => __('Get Payment Update'),
            'onclick' => 'setLocation(\'' . $this->url->getUrl('przelewy24/payment/review', [
                'order_id' => $order->getEntityId(),
            ]) . '\')',
        ]);
    }
}
