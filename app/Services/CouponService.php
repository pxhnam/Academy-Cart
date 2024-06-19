<?php

namespace App\Services;

use App\Enums\CouponType;
use Illuminate\Support\Carbon;
use App\Services\Interfaces\CouponServiceInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class CouponService implements CouponServiceInterface
{
    protected $couponRepository;
    protected $orderRepository;
    public function __construct(
        CouponRepositoryInterface $couponRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->couponRepository = $couponRepository;
        $this->orderRepository = $orderRepository;
    }
    // public function makeDiscountCost($code, $cost)
    // {
    //     if (!$code || $cost <= 0) return 0;
    //     $coupon = $this->couponRepository->findByCode($code);
    //     if (!$coupon) return 0;
    //     $now = Carbon::now();
    //     if ($coupon->start_date > $now || $coupon->expiry_date < $now) return 0;
    //     if ($coupon->usage_limit !== null && $coupon->usage_limit <= $coupon->usage_count) return 0;
    //     if ($cost < $coupon->min_amount) return 0;
    //     if ($coupon->type === CouponType::FIXED) {
    //         return $coupon->value;
    //     } elseif ($coupon->type === CouponType::PERCENT) {
    //         $discount = $cost * ($coupon->value / 100);
    //         return $discount > $coupon->max_amount ? $coupon->max_amount : $discount;
    //     }
    //     return 0;
    // }

    // // public function findValidCouponsByCost($total)
    // // {
    // //     $listPromotions = $this->orderRepository->getUsedPromotionsForUser();
    // //     $listPromotions = json_decode($listPromotions, true);
    // //     $usedCodes = [];

    // //     if (is_array($listPromotions)) {
    // //         foreach ($listPromotions as $promotions) {
    // //             $promotions = json_decode($promotions, true);
    // //             if (is_array($promotions)) {
    // //                 foreach ($promotions as $promotion) {
    // //                     if (isset($promotion['code']) && $this->couponRepository->checkValidCode($promotion['code'])) {
    // //                         $usedCodes[] = trim($promotion['code']);
    // //                     }
    // //                 }
    // //             }
    // //         }
    // //     }

    // //     $validCoupons = $this->couponRepository->findValidCouponsByCost($total);

    // //     $filteredCoupons = $validCoupons->reject(function ($coupon) use ($usedCodes) {
    // //         return in_array($coupon->code, $usedCodes);
    // //     });

    //     return $filteredCoupons;
    // }

    public function checkValidCode($code)
    {
        return $this->couponRepository->checkValidCode($code);
    }

    public function findByCode($code)
    {
        return $this->couponRepository->findByCode($code);
    }
}
