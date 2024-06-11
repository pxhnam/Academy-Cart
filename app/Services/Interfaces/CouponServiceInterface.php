<?php

namespace App\Services\Interfaces;

interface CouponServiceInterface
{
    public function makeDiscountCost($code, $cost);
    public function findByCode($code);
}
