<?php

namespace App\Services\Interfaces;

interface MomoServiceInterface
{
    public function createQR($request);
    public function finishedPayment($request);
}
