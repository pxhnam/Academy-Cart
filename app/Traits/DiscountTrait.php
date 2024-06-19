<?php

namespace App\Traits;

use App\Models\Coupon;
use App\Enums\CouponType;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\DiscountConditionRepositoryInterface;

trait DiscountTrait
{
    protected $couponRepository;
    protected $conditionRepository;

    public function initializeDiscountTrait(
        CouponRepositoryInterface $couponRepository,
        DiscountConditionRepositoryInterface $conditionRepository
    ) {
        $this->couponRepository = $couponRepository;
        $this->conditionRepository = $conditionRepository;
    }

    public function makeDiscountCost($code, $cost)
    {
        if (!$code || $cost <= 0) return 0;

        $usedCodes = $this->getUsedCodes();
        if (in_array($code, $usedCodes)) return 0;

        $coupon = $this->couponRepository->findValidCode($code);
        if (!$coupon) return 0;
        if ($cost < $coupon->min_amount) return 0;
        if ($coupon->type === CouponType::FIXED) {
            return $coupon->value;
        } elseif ($coupon->type === CouponType::PERCENT) {
            $discount = $cost * ($coupon->value / 100);
            return $discount > $coupon->max_amount ? $coupon->max_amount : $discount;
        }
        return 0;
    }

    public function findValidCouponsByCost($total)
    {
        return $this->couponRepository->findValidCouponsByCost($this->getUsedCodes(), $total);
    }
    public function getUsedCodes()
    {
        $listPromotions = $this->orderRepository->getUsedPromotionsForUser();
        $listPromotions = json_decode($listPromotions, true);
        $usedCodes = [];

        if (is_array($listPromotions)) {
            foreach ($listPromotions as $promotions) {
                $promotions = json_decode($promotions, true);
                if (is_array($promotions)) {
                    foreach ($promotions as $promotion) {
                        if (isset($promotion['code']) && $this->couponRepository->checkValidCode($promotion['code'])) {
                            $usedCodes[] = trim($promotion['code']);
                        }
                    }
                }
            }
        }
        return $usedCodes;
    }

    public function limitTest($total, $discount)
    {
        $condition = $this->conditionRepository->first();

        if ($condition) {
            $maxDiscount = $total * ($condition->maximum / 100);

            if ($discount >= $maxDiscount) {
                return [false, $maxDiscount];
            }

            return [true, $discount];
        }
        return [false, 0];
    }


    // public function makeDiscount($codes, $cost)
    // {
    //     $discount = 0;
    //     if (!empty($codes)) {
    //         foreach ($codes as $code) {
    //             $discount += $this->makeDiscountCost($code, $cost);
    //         }
    //         $maxDiscount = $this->limitTest($cost, $discount);
    //         if (is_numeric($maxDiscount)) {
    //             $discount = $maxDiscount;
    //         }
    //     }
    //     return $discount;
    // }
}
