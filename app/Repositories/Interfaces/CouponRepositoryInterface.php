<?php

namespace App\Repositories\Interfaces;

interface CouponRepositoryInterface
{
    public function findByCode($code);
    public function findValidCouponsByCost($cost);
}
