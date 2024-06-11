<?php

namespace App\Services\Interfaces;

interface VNPayServiceInterface
{
    public function processPayment($request);
    public function finishedPayment($request);
}
