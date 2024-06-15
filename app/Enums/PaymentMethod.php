<?php

namespace App\Enums;

class PaymentMethod
{
    const VNPay = 'VNPay';
    const QR_VNPay = 'QR_VNPay';
    const QR_Momo = 'QR_Momo';

    public static function getValues()
    {
        return [
            self::VNPay,
            self::QR_VNPay,
            self::QR_Momo
        ];
    }
}
