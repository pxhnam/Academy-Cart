<?php

namespace App\Enums;

class PaymentMethod
{
    const VNPay = 'VNPay';
    const ZaloPay = 'ZaloPay';
    const Momo = 'Momo';

    public static function getValues()
    {
        return [
            self::VNPay,
            self::ZaloPay,
            self::Momo
        ];
    }
}
