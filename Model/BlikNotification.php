<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model;

use Magento\Framework\Model\AbstractModel;
use PayPro\Przelewy24\Api\Data\BlikNotificationInterface;
use PayPro\Przelewy24\Model\ResourceModel\BlikNotification as ResourceModel;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class BlikNotification extends AbstractModel implements BlikNotificationInterface
{
    private const NO_ERROR = '0';

    private const PREDEFINED_MESSAGES = [
        'ER_WRONG_TICKET' => 'Incorrect BLIK code was entered. Try again.',
        'ER_TIC_EXPIRED' => 'Incorrect BLIK code was entered. Try again.',
        'ER_TIC_STS' => 'Incorrect BLIK code was entered. Try again.',
        'ER_TIC_USED' => 'Incorrect BLIK code was entered. Try again.',
        'INSUFFICIENT_FUNDS' => 'Payment failed. Check the reason in the banking application and try again.',
        'LIMIT_EXCEEDED' => 'Payment failed. Check the reason in the banking application and try again.',
        'USER_TIMEOUT' => 'Payment failed - not confirmed on time in the banking application. Try again.',
        'TIMEOUT' => 'Payment failed - not confirmed on time in the banking application. Try again.',
        'AM_TIMEOUT' => 'Payment failed - not confirmed on time in the banking application. Try again.',
        'ER_BAD_PIN' => 'Payment failed. Check the reason in the banking application and try again.',
        'USER_DECLINED' => 'Payment failed. Check the reason in the banking application and try again.',
    ];

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getSessionId(): string
    {
        return (string) $this->getData(self::SESSION_ID);
    }

    public function setSessionId(string $sessionId): void
    {
        $this->setData(self::SESSION_ID, $sessionId);
    }

    public function getContent(): array
    {
        return (array) $this->getData(self::CONTENT);
    }

    public function setContent(array $content): void
    {
        $this->setData(self::CONTENT, $content);
    }

    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    public function getOrderId(): int
    {
        return (int) ($this->getContent()[self::ORDER_ID] ?? 0);
    }

    public function getMethod(): int
    {
        return (int) ($this->getContent()[self::METHOD] ?? 0);
    }

    public function getError(): string
    {
        return (string) ($this->getContent()[self::RESULT][self::ERROR] ?? '');
    }

    public function getMessage(): string
    {
        $message = (string) ($this->getContent()[self::RESULT][self::MESSAGE] ?? '');
        if (isset(self::PREDEFINED_MESSAGES[$message])) {
            // phpcs:ignore Magento2.Translation.ConstantUsage.VariableTranslation
            return (string) __(self::PREDEFINED_MESSAGES[$message]);
        }

        return $message;
    }

    public function getStatus(): string
    {
        return (string) ($this->getContent()[self::RESULT][self::STATUS] ?? '');
    }

    public function isSuccess(): bool
    {
        return $this->getError() === self::NO_ERROR;
    }
}
