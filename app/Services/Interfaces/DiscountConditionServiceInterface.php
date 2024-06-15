<?php

namespace App\Services\Interfaces;

interface DiscountConditionServiceInterface
{
    public function limitTest($total, $discount);
}
