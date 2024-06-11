<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Enums\CouponType;
use App\Models\Cart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('coupons')->insert([
            'code' => 'GIAM10K',
            'value' => 10000,
            'type' => CouponType::FIXED,
            'description' => 'giam 10k ne',
            'min_amount' => 50000,
            // 'max_amount' => 50,
            'start_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addDays(rand(1, 30)),
            // 'usage_limit' => null,
            'usage_count' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
