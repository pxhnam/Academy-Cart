<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;

class CouponRepository implements CouponRepositoryInterface
{
    private $model;
    private $now;
    public function __construct()
    {
        $this->model = Coupon::class;
        $this->now = Carbon::now();
    }

    public function findByCode($code)
    {
        return $this->model::where('code', $code)->first();
    }

    public function findValidCouponsByCost($codes, $total)
    {
        return $this->model::select('code', 'description')
            ->whereNotIn('code', $codes)
            ->where('min_amount', '<=', $total)
            ->where('start_date', '<=', $this->now)
            ->where('expiry_date', '>=', $this->now)
            ->where(function ($query) {
                $query->whereColumn('usage_count', '<', 'usage_limit')
                    ->orWhereNull('usage_limit');
            })
            ->get();
    }

    public function findValidCode($code)
    {
        return $this->model::where('code', $code)
            ->where('start_date', '<=', $this->now)
            ->where('expiry_date', '>=', $this->now)
            ->where(function ($query) {
                $query->whereColumn('usage_count', '<', 'usage_limit')
                    ->orWhereNull('usage_limit');
            })
            ->first();
    }

    public function checkValidCode($code)
    {
        return $this->model::where('code', $code)
            ->where('start_date', '<=', $this->now)
            ->where('expiry_date', '>=', $this->now)
            ->where(function ($query) {
                $query->whereColumn('usage_count', '<', 'usage_limit')
                    ->orWhereNull('usage_limit');
            })
            ->exists();
    }
}
