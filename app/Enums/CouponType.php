<?php

namespace App\Enums;

class CouponType
{
    const PERCENT = 'percent';
    const FIXED = 'fixed';

    public static function getValues()
    {
        return [
            self::FIXED,
            self::PERCENT
        ];
    }
}
