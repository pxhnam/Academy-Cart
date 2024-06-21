<?php

namespace App\Services\Interfaces;

interface OrderServiceInterface
{
    public function show();
    public function find($id);
    public function createOrder();
    public function bill($orderId);
    // public function getUsedCodesByUser();
}
