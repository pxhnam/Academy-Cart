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
use Exception;

class OrderService implements OrderServiceInterface
{
    use DiscountTrait;

    protected $orderRepository;
    protected $orderDetailRepository;
    protected $cartRepository;
    protected $courseRepository;
    protected $couponRepository;
    protected $conditionRepository;
    private $userId;


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
        $this->userId = Auth::user()->id;
    }

    public function show()
    {
        try {
            $ids = Session::get('carts') ?? [];
            $codes = Session::get('codes') ?? [];
            $discount = 0;

            list($carts, $base, $total) = $this->getInfoCourseByCart($ids);

            foreach ($carts as &$cart) {
                $cart['cost'] = NumberFormat::VND($cart['cost']);
            }

            $discount = $this->makeDiscount($codes, $total);

            return [
                'carts' => $carts,
                'base' => NumberFormat::VND($base),
                'reduce' => NumberFormat::VND($base - $total),
                'discount' => NumberFormat::VND($discount),
                'total' => NumberFormat::VND($total - $discount),
                'paymentMethods' => PaymentMethod::getValues()
            ];
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public function createOrder()
    {
        return DB::transaction(function () {
            $ids = Session::get('carts') ?? [];
            $codes = Session::get('codes') ?? [];
            $promotion = [];
            $discount = 0;

            list($courses, $base, $total) = $this->getInfoCourseByCart($ids);

            if (!empty($codes)) {
                foreach ($codes as $code) {
                    $promotion[] = $this->couponRepository->findValidCode($code);
                }
                $discount = $this->makeDiscount($codes, $total);
            }

            $total -= $discount;
            $order = $this->orderRepository->create([
                'user_id' => $this->userId,
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

    public function makeDiscount($codes, $total)
    {
        $discount = 0;
        try {
            if (!empty($codes)) {
                foreach ($codes as $code) {
                    $discount += $this->makeDiscountCost($code, $total);
                }
                list($test, $limit) = $this->limitTest($total, $discount);
                if (!$test) {
                    $discount = $limit;
                }
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
        return $discount;
    }

    public function getInfoCourseByCart($ids)
    {
        $base = 0; //base price
        $total = 0;
        $courses = [];
        try {
            foreach ($ids as $id) {
                $cart = $this->cartRepository->findById($id);
                if ($cart) {
                    $course = $this->courseRepository->find($cart->course_id);
                    if ($course['success']) {
                        $course = $course['course'];
                        $base += $course['fake_cost'];
                        $total += $course['cost'];
                        $courses[] = [
                            'course_id' => $course['id'],
                            'thumbnail' => $course['thumbnail'],
                            'course_name' => $course['name'],
                            'lecturer' => $course['lecturer'],
                            'cost' => $course['cost'],
                            'duration' => $course['duration'],
                        ];
                    }
                }
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
        return [$courses, $base, $total];
    }

    public function bill($orderId)
    {
        try {
            $order = $this->orderRepository->getWithDetails($orderId);
            $order->total = NumberFormat::VND($order->total);

            foreach ($order->details as $detail) {
                $detail->cost = NumberFormat::VND($detail->cost);
                $course = $this->courseRepository->find($detail->course_id);
                if ($course) {
                    $detail->thumbnail = $course['course']['thumbnail'];
                    $detail->lecturer = $course['course']['lecturer'];
                }
            }
            return [
                'total' => $order->total,
                'details' => $order->details,
                'count' => $order->details->count(),
            ];
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
