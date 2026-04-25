<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api;

use Magento\Sales\Api\Data\TransactionInterface;
use PayPro\Przelewy24\Api\Data\ApiInfoInterface;

interface TransactionRepositoryInterface
{
    public function get(ApiInfoInterface $info): TransactionInterface;

    public function save(ApiInfoInterface $info, TransactionInterface $magentoTransaction): void;
}
