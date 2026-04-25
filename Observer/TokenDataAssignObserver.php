<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class TokenDataAssignObserver extends AbstractDataAssignObserver
{
    const TOKEN = 'token';
    const SESSION_ID = 'sessionId';

    /**
     * @var string[]
     */
    private $additionalInformationList = [
        self::TOKEN,
        self::SESSION_ID,
    ];

    public function execute(Observer $observer): void
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
