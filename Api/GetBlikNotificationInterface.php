<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\BlikNotificationInterface;

interface GetBlikNotificationInterface
{
    /**
     * @param string $sessionId
     * @return \PayPro\Przelewy24\Api\Data\BlikNotificationInterface|null
     */
    public function execute(string $sessionId): ?BlikNotificationInterface;
}
