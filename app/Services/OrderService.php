<?php

namespace App\Services;

use App\Enums\CartState;
use App\Enums\PaymentMethod;
use App\Helpers\NumberFormat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\CouponServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\OrderDetailRepositoryInterface;

class OrderService implements OrderServiceInterface
{

    private $orderRepository;
    private $orderDetailRepository;
    private $cartRepository;
    private $courseRepository;
    private $couponService;
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderDetailRepositoryInterface $orderDetailRepository,
        CartRepositoryInterface $cartRepository,
        CourseRepositoryInterface $courseRepository,
        CouponServiceInterface $couponService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->cartRepository = $cartRepository;
        $this->courseRepository = $courseRepository;
        $this->couponService = $couponService;
    }

    public function show()
    {
        $ids = Session::get('carts') ?? [];
        $code = Session::get('code') ?? null;
        $carts = [];
        $cost = 0;
        $discount = 0;
        $total = 0;
        foreach ($ids as $id) {
            $cart = $this->cartRepository->findById($id);
            if ($cart) {
                $course = $this->courseRepository->find($cart->course_id);
                if ($course['success']) {
                    $course = $course['course'];
                    $cost += $course['cost'];
                    $carts[] = [
                        'thumbnail' => $course['thumbnail'],
                        'name' => $course['name'],
                        'cost' => NumberFormat::VND($course['cost'])
                    ];
                }
            }
        }

        if ($code) {
            $discount = $this->couponService->makeDiscountCost($code, $cost);
        }
        $total = $cost - $discount;

        return [
            'carts' => $carts,
            'cost' => NumberFormat::VND($cost),
            'discount' => NumberFormat::VND($discount),
            'total' => NumberFormat::VND($total),
            'paymentMethods' => PaymentMethod::getValues()
        ];
    }

    public function createOrder()
    {
        return DB::transaction(function () {
            $userId = Auth::user()->id;
            $discount = 0;
            $total = 0;
            $ids = Session::get('carts') ?? [];
            $code = Session::get('code') ?? null;
            $courses = [];
            foreach ($ids as $id) {
                $cart = $this->cartRepository->findById($id);
                if ($cart) {
                    $course = $this->courseRepository->find($cart->course_id);
                    if ($course['success']) {
                        $course = $course['course'];
                        $total += $course['cost'];
                        $courses[] = [
                            'course_id' => $course['id'],
                            'course_name' => $course['name'],
                            'cost' => $course['cost'],
                        ];
                    }
                }
            }
            if ($code) {
                $discount = $this->couponService->makeDiscountCost($code, $total);
            }
            $total -= $discount;
            $coupon = $this->couponService->findByCode($code);
            $couponId = $coupon->id ?? null;
            $orderData = [
                'user_id' => $userId,
                'coupon_id' => $couponId,
                'coupon_code' => $code,
                'discount' => $discount,
                'total' => $total,
            ];
            $order = $this->orderRepository->create($orderData);
            $orderId = $order->id;
            foreach ($courses as $course) {
                $course['order_id'] = $orderId;
                $this->orderDetailRepository->create($course);
            }

            return [
                'order_id' => $orderId,
                'total' => $total
            ];
        });
    }
    public function find($id)
    {
        return $this->orderRepository->find($id);
    }
}
