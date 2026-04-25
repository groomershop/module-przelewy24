<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\BlikResponseInterface;

interface PayBlikInterface
{
    /**
     * @param string $sessionId
     * @param string $blikCode
     * @return \PayPro\Przelewy24\Api\Data\BlikResponseInterface
     */
    public function execute(string $sessionId, string $blikCode): BlikResponseInterface;
}
