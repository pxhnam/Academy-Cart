<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    private $model;
    public function __construct()
    {
        $this->model = Order::class;
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function create($data)
    {
        return $this->model::create($data);
    }
}
