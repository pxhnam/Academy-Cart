<?php

namespace App\Enums;

class CartState
{
    const PENDING = 'pending';
    const PURCHASED = 'purchased';
    const REMOVED = 'removed';

    public static function getValues()
    {
        return [
            self::PENDING,
            self::PURCHASED,
            self::REMOVED,
        ];
    }
}
