<?php

namespace App\Services\Interfaces;

interface CartServiceInterface
{
    public function list();
    public function add($courseId);
    public function summary($data);
    public function checkout($data);
    public function removeCode();
    public function remove($id);
}
