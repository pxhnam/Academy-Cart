<?php

namespace App\Services;

use App\Enums\CouponType;
use Illuminate\Support\Carbon;
use App\Services\Interfaces\CouponServiceInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;


class CouponService implements CouponServiceInterface
{
    private $couponRepository;
    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }
    public function makeDiscountCost($code, $cost)
    {
        if (!$code || $cost <= 0) return 0;
        $coupon = $this->couponRepository->findByCode($code);
        if (!$coupon) return 0;
        $now = Carbon::now();
        if ($coupon->start_date > $now || $coupon->expiry_date < $now) return 0;
        if ($coupon->usage_limit !== null && $coupon->usage_limit <= $coupon->usage_count) return 0;
        if ($cost < $coupon->min_amount) return 0;
        if ($coupon->type === CouponType::FIXED) {
            return $coupon->value;
        } elseif ($coupon->type === CouponType::PERCENT) {
            $discount = $cost * ($coupon->value / 100);
            return $discount > $coupon->max_amount ? $coupon->max_amount : $discount;
        }
        return 0;
    }
    public function findByCode($code)
    {
        return $this->couponRepository->findByCode($code);
    }
}
