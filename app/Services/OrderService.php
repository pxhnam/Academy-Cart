<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Enums\PaymentMethod;
use App\Helpers\NumberFormat;
use App\Traits\DiscountTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\OrderDetailRepositoryInterface;
use App\Repositories\Interfaces\DiscountConditionRepositoryInterface;

class OrderService implements OrderServiceInterface
{
    use DiscountTrait;

    protected $orderRepository;
    protected $orderDetailRepository;
    protected $cartRepository;
    protected $courseRepository;
    protected $couponRepository;
    protected $conditionRepository;


    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderDetailRepositoryInterface $orderDetailRepository,
        CartRepositoryInterface $cartRepository,
        CourseRepositoryInterface $courseRepository,
        CouponRepositoryInterface $couponRepository,
        DiscountConditionRepositoryInterface $conditionRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->cartRepository = $cartRepository;
        $this->courseRepository = $courseRepository;
        $this->couponRepository = $couponRepository;
        $this->conditionRepository = $conditionRepository;
        $this->initializeDiscountTrait($couponRepository, $conditionRepository);
    }

    public function show()
    {
        $ids = Session::get('carts') ?? [];
        $codes = Session::get('codes') ?? [];
        $discount = 0;

        list($carts, $total) = $this->getInfoCourseByCart($ids);

        foreach ($carts as &$cart) {
            $cart['cost'] = NumberFormat::VND($cart['cost']);
        }

        $discount = $this->makeDiscount($codes, $total);

        return [
            'carts' => $carts,
            'cost' => NumberFormat::VND($total),
            'discount' => NumberFormat::VND($discount),
            'total' => NumberFormat::VND($total - $discount),
            'paymentMethods' => PaymentMethod::getValues()
        ];
    }

    public function createOrder()
    {
        return DB::transaction(function () {
            $userId = Auth::user()->id;
            $ids = Session::get('carts') ?? [];
            $codes = Session::get('codes') ?? [];
            $promotion = [];
            $discount = 0;

            list($courses, $total) = $this->getInfoCourseByCart($ids);

            if (!empty($codes)) {
                foreach ($codes as $code) {
                    //$discount += $this->makeDiscountCost($code, $total);
                    $promotion[] = $this->couponRepository->findValidCode($code);
                }
                $discount = $this->makeDiscount($codes, $total);
                // list($test, $limit) = $this->limitTest($total, $discount);
                // if (!$test) {
                //     $discount = $limit;
                // }
            }

            $total -= $discount;
            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'promotion' => json_encode($promotion),
                'discount' => $discount,
                'total' => $total,
            ]);
            $orderId = $order->id;

            foreach ($courses as $course) {
                $course['order_id'] = $orderId;
                $this->orderDetailRepository->create($course);
            }
            return [
                'order_id' => $orderId,
                'total' => $total,
            ];
        });
    }

    public function find($id)
    {
        return $this->orderRepository->find($id);
    }

    public function makeDiscount($codes, $total)
    {
        $discount = 0;
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $discount += $this->makeDiscountCost($code, $total);
            }
            list($test, $limit) = $this->limitTest($total, $discount);
            if (!$test) {
                $discount = $limit;
            }
        }
        return $discount;
    }

    public function getInfoCourseByCart($ids)
    {
        $total = 0;
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
                        'thumbnail' => $course['thumbnail'],
                        'course_name' => $course['name'],
                        'cost' => $course['cost'],
                    ];
                }
            }
        }
        return [$courses, $total];
    }
}
