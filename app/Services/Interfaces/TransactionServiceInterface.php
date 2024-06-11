<?php

namespace App\Services\Interfaces;

interface TransactionServiceInterface
{
    public function VNPay($orderId);
}
