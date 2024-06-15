<?php

namespace App\Services;

use App\Enums\CartState;
use App\Helpers\APIResponse;
use App\Helpers\NumberFormat;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\CartServiceInterface;
use App\Services\Interfaces\CouponServiceInterface;
use App\Services\Interfaces\CourseServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Services\Interfaces\DiscountConditionServiceInterface;

class CartService implements CartServiceInterface
{
    private $cartRepository;
    private $courseService;
    private $couponService;
    private $conditionService;
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CourseServiceInterface $courseService,
        CouponServiceInterface $couponService,
        DiscountConditionServiceInterface $conditionService
    ) {
        $this->cartRepository = $cartRepository;
        $this->courseService = $courseService;
        $this->couponService = $couponService;
        $this->conditionService = $conditionService;
    }

    public function list()
    {
        $carts = $this->cartRepository->listCart();
        $courses = [];
        if ($carts->count() > 0) {
            foreach ($carts as $cart) {
                $course = $this->courseService->find($cart->course_id);
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
        if ($this->courseService->check($courseId)) {
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
        if (!empty($ids)) {
            $total = $this->makeTotalCarts($ids);
        }
        if (!empty($codes)) {
            foreach ($codes as $key => $code) {
                $reduce = $this->couponService->makeDiscountCost($code, $total);
                if ($reduce) {
                    $discount += $reduce;
                } else {
                    unset($codes[$key]);
                }
            }
            $maxDiscount = $this->conditionService->limitTest($total, $discount);
            if (is_numeric($maxDiscount)) {
                $discount = $maxDiscount;
            }
        }
        return APIResponse::make(
            true,
            'success',
            '',
            [
                'cost' => NumberFormat::VND($total),
                'discount' => NumberFormat::VND($discount),
                'total' => NumberFormat::VND($total - $discount),
                'codes' => $codes
            ] + ($total !== 0 ? ['coupons' => $this->couponService->findValidCouponsByCost($total)] : [])
        );
    }

    public function checkout($data)
    {
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
                    $reduce = $this->couponService->makeDiscountCost($code, $total);
                    if ($reduce == 0) {
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
                $course = $this->courseService->find($cart->course_id);
                if ($course['success']) {
                    $total += $course['course']['cost'];
                }
            }
        }
        return $total;
    }
}
