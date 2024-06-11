<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;

class CouponRepository  implements CouponRepositoryInterface
{
    private $model;
    public function __construct()
    {
        $this->model = Coupon::class;
    }

    public function findByCode($code)
    {
        return $this->model::where('code', $code)->first();
    }
    public function findValidCouponsByCost($cost)
    {
        $now = Carbon::now();
        return $this->model::where('min_amount', '<=', $cost)
            ->where('start_date', '<=', $now)
            ->where('expiry_date', '>=', $now)
            ->where(function ($query) {
                $query->whereColumn('usage_count', '<', 'usage_limit')
                    ->orWhereNull('usage_limit');
            })
            ->get();
    }
}
