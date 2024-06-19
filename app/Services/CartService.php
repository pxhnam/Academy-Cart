<?php

namespace App\Services;

use App\Enums\CartState;
use App\Enums\CouponType;
use App\Helpers\APIResponse;
use App\Helpers\NumberFormat;
use App\Traits\DiscountTrait;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\CartServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\DiscountConditionRepositoryInterface;

class CartService implements CartServiceInterface
{
    use DiscountTrait;
    protected $cartRepository;
    protected $courseRepository;
    protected $couponRepository;
    protected $conditionRepository;
    protected $orderRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CourseRepositoryInterface $courseRepository,
        CouponRepositoryInterface $couponRepository,
        DiscountConditionRepositoryInterface $conditionRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->courseRepository = $courseRepository;
        $this->conditionRepository = $conditionRepository;
        $this->couponRepository = $couponRepository;
        $this->orderRepository = $orderRepository;
        $this->initializeDiscountTrait($couponRepository, $conditionRepository);
    }

    public function list()
    {
        $carts = $this->cartRepository->listCart();
        $courses = [];
        if ($carts->count() > 0) {
            foreach ($carts as $cart) {
                $course = $this->courseRepository->find($cart->course_id);
                if ($course['success']) {
                    $course = $course['course'];
                    $course['id'] = $cart->id; #use cart_id
                    $course['cost'] = NumberFormat::VND($course['cost']);
                    $courses[] = $course;
                }
            }
            return APIResponse::make(true, 'success', '', $courses);
        }
        return APIResponse::make(false, 'error', '');
    }

    public function add($courseId)
    {
        #Check for valid course
        if ($this->courseRepository->check($courseId)) {
            $cart = $this->cartRepository->findByIdCourse($courseId);
            #Check the cart exists
            if ($cart) {
                switch ($cart->state) {
                    case CartState::PENDING:
                        return APIResponse::make(true, 'info', 'Khóa học đã có trong giỏ hàng.');
                    case CartState::REMOVED:
                        $cart->state = CartState::PENDING;
                        $cart->save();
                        return APIResponse::make(true, 'success', 'Đã thêm khóa học vào giỏ hàng.');
                    case CartState::PURCHASED:
                        return APIResponse::make(true, 'info', 'Bạn đã mua khóa học này.');
                    default:
                        break;
                }
            } else {
                $newCart = $this->cartRepository->add($courseId);
                if ($newCart) {
                    return APIResponse::make(true, 'success', 'Đã thêm khóa học vào giỏ hàng.');
                } else {
                    return APIResponse::make(false, 'error', 'Vui lòng thử lại sau.');
                }
            }
        } else {
            return APIResponse::make(false, 'error', 'Khóa học không tồn tại.');
        }
    }

    public function summary($data)
    {
        $ids = $data->ids ?? [];
        $ids = array_unique($ids);
        $codes = $data->codes ?? [];
        $codes = array_map('strtoupper', $codes);
        $codes = array_unique($codes);
        $discount = 0;
        $total = 0;
        $coupons = [];
        $message = '';
        if (!empty($ids)) {
            $total = $this->makeTotalCarts($ids);

            $coupons = [
                'data' => $this->findValidCouponsByCost($total),
                'limit' => false
            ];
        }
        if (!empty($codes)) {
            foreach ($codes as $key => $code) {
                $reduce = $this->makeDiscountCost($code, $total);
                if ($reduce) {
                    $discount += $reduce;
                    list($test, $limit) = $this->limitTest($total, $discount);
                    if (!$test) {
                        $discount = $limit;
                        $coupons['limit'] = true;
                        break;
                    }
                } else {
                    if (!empty($ids)) $message = 'Mã giảm giá không hợp lệ';
                    unset($codes[$key]);
                }
            }
        }
        return APIResponse::make(
            true,
            'info',
            $message,
            [
                'cost' => NumberFormat::VND($total),
                'discount' => NumberFormat::VND($discount),
                'total' => NumberFormat::VND($total - $discount),
                'codes' => $codes,
                'coupons' => $coupons
            ]
        );
    }

    public function checkout($data)
    {
        Session::forget(['carts', 'codes']);
        $ids = $data->ids ?? [];
        $ids = array_unique($ids);
        $codes = $data->codes ?? [];
        $codes = array_map('strtoupper', $codes);
        $codes = array_unique($codes);
        $carts = [];
        $total = 0;
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $cart = $this->cartRepository->findById($id);
                if ($cart) {
                    $carts[] = $id;
                } else {
                    $carts = [];
                    return false;
                }
            }
        }
        if (!empty($carts)) {
            Session::put('carts', $carts);
            if (!empty($codes)) {
                $total = $this->makeTotalCarts($ids);
                foreach ($codes as $key => $code) {
                    $reduce = $this->makeDiscountCost($code, $total);
                    if ($reduce === 0) {
                        unset($codes[$key]);
                    }
                }
                Session::put('codes', $codes);
            }
            return APIResponse::make(true, 'success', '', ['link' => route('checkout')]);
        } else {
            return APIResponse::make(false, 'info', 'Bạn chưa chọn khóa học nào');
        }
    }

    public function remove($id)
    {
        // Gate::authorize('delete', $cart);
        $removeCart = $this->cartRepository->removeFromCart($id);
        if ($removeCart) {
            $count = $this->cartRepository->countCart();
            return APIResponse::make(true, 'success', 'Đã xóa khóa học khỏi giỏ hàng.', $count);
        }
    }

    public function makeTotalCarts($ids)
    {
        $total = 0;
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $cart = $this->cartRepository->findById($id);
                $course = $this->courseRepository->find($cart->course_id);
                if ($course['success']) {
                    $total += $course['course']['cost'];
                }
            }
        }
        return $total;
    }

    public function listRecommend()
    {
        $coursesId = $this->cartRepository->getCoursesIdNotInCart();
        return $this->courseRepository->getRandomCoursesNotInCart($coursesId);
    }
}
