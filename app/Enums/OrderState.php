<?php

namespace App\Enums;

class OrderState
{
    const PENDING = 'pending';
    const PAID  = 'paid';
    const FAILED = 'failed';

    public static function getValues()
    {
        return [
            self::PENDING,
            self::PAID,
            self::FAILED,
        ];
    }
}
