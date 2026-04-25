<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class CardDataAssignObserver extends AbstractDataAssignObserver
{
    const REF_ID = 'refId';
    const CARD_TYPE = 'cardType';
    const CARD_MASK = 'cardMask';
    const CARD_DATE  = 'cardDate';
    const SESSION_ID = 'sessionId';

    /**
     * @var string[]
     */
    private $additionalInformationList = [
        self::REF_ID,
        self::CARD_TYPE,
        self::CARD_MASK,
        self::CARD_DATE,
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
