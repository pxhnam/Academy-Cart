<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface
{
    public function listCart();
    public function getCoursesIdNotInCart();
    public function countCart();
    public function add($courseId);
    public function findByIdCourse($courseId);
    public function findById($id);
    public function removeFromCart($id);
}
