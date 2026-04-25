<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use PayPro\Przelewy24\Api\Data\CardTokenizationPayloadInterface;

interface GetCardTokenizationPayloadInterface
{
    /**
     * @return \PayPro\Przelewy24\Api\Data\CardTokenizationPayloadInterface
     */
    public function execute(): CardTokenizationPayloadInterface;
}
