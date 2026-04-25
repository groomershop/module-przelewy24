<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

use PayPro\Przelewy24\Model\Api\ApiAmount;
use PayPro\Przelewy24\Model\Api\ApiSignature;

interface TransactionPayloadInterface
{
    const MERCHANT_ID = 'merchantId';
    const POS_ID = 'posId';
    const SESSION_ID = 'sessionId';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const DESCRIPTION = 'description';
    const EMAIL = 'email';
    const CLIENT = 'client';
    const ADDRESS = 'address';
    const ZIP = 'zip';
    const CITY = 'city';
    const COUNTRY = 'country';
    const LANGUAGE = 'language';
    const METHOD = 'method';
    const URL_RETURN = 'urlReturn';
    const URL_STATUS = 'urlStatus';
    const WAIT_FOR_RESULT = 'waitForResult';
    const SIGN = 'sign';
    const ENCODING = 'encoding';
    const METHOD_REF_ID = 'methodRefId';
    const PAYLOAD = 'payload';
    const TYPE = 'type';
    const CARD_DATA = 'cardData';
    const MEANS = 'means';
    const REFERENCE_NUMBER = 'referenceNumber';
    const ID = 'id';
    const X_PAY_PAYLOAD = 'xPayPayload';
    const TRANSACTION_TYPE = 'transactionType';
    const REFERENCE_REGISTER = 'referenceRegister';
    const URL_CARD_PAYMENT_NOTIFICATION = 'urlCardPaymentNotification';

    const TYPE_STANDARD = 'standard';
    const TYPE_INITIAL = 'initial';
    const TYPE_1CLICK = '1click';
    const TYPE_RECURRING = 'recurring';

    const STATUS_ROUTE = 'przelewy24/status/transaction';
    const RETURN_ROUTE = 'checkout/onepage/success';
    const BLIK_NOTIFICATION_ROUTE = 'przelewy24/status/blik';

    const PAYMENT_RETURN_ROUTE = 'p24_return_route';

    /**
     * @return \PayPro\Przelewy24\Model\Api\ApiAmount
     */
    public function getAmount(): ApiAmount;

    /**
     * @return \PayPro\Przelewy24\Model\Api\ApiSignature
     */
    public function getSignature(): ApiSignature;

    /**
     * @param string $crcKey
     * @return array
     */
    public function get(string $crcKey): array;
}
