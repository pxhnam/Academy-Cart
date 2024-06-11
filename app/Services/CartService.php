<?php

namespace App\Services;

use Carbon\Carbon;
use App\Enums\CartState;
use App\Enums\CouponType;
use App\Helpers\APIResponse;
use App\Helpers\NumberFormat;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\CartServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CartService implements CartServiceInterface
{
    private $cartRepository;
    private $courseRepository;
    private $couponRepository;
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CourseRepositoryInterface $courseRepository,
        CouponRepositoryInterface $couponRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->courseRepository = $courseRepository;
        $this->couponRepository = $couponRepository;
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
        $ids = $data['ids'] ?? [];
        $code = $data['code'] ?? null;
        $discount = 0;
        $total = 0;
        foreach ($ids as $id) {
            $cart = $this->cartRepository->findById($id);
            if ($cart) {
                $course = $this->courseRepository->find($cart->course_id);
                if ($course['success']) {
                    $total += $course['course']['cost'];
                }
            }
        }
        if ($code) {
            $coupon = $this->couponRepository->findByCode($code);
            if ($coupon) {
                $now = Carbon::now();
                if ($coupon->start_date > $now || $coupon->expiry_date < $now) {
                    $this->removeCode();
                    return APIResponse::make(false, 'info', 'Mã giảm giá đã hết hạn.');
                }
                if ($coupon->usage_limit !== null && $coupon->usage_limit <= $coupon->usage_count) {
                    $this->removeCode();
                    return APIResponse::make(false, 'info', 'Mã đã đạt giới hạn sử dụng.');
                }
                if ($coupon->type === CouponType::FIXED) {
                    $discount = $coupon->value;
                } elseif ($coupon->type === CouponType::PERCENT) {
                    $discount = $total * ($coupon->value / 100);
                    if ($discount > $coupon->max_amount) {
                        $discount = $coupon->max_amount;
                    }
                } else {
                    return APIResponse::make(false, 'info', 'Không xác định.');
                }
                if ($total < $coupon->min_amount) {
                    $discount = 0;
                    return APIResponse::make(false, 'info', 'Hãy chọn thêm khóa học để sử dụng mã này.');
                }
            } else return APIResponse::make(false, 'info', 'Mã giảm giá không hợp lệ.');
        }

        return APIResponse::make(
            true,
            '',
            '',
            [
                'cost' => NumberFormat::VND($total),
                'discount' => NumberFormat::VND($discount),
                'total' => NumberFormat::VND($total - $discount),
            ] + (trim($code) ? ['code' => strtoupper($code)] : [])
                + ($total !== 0 ? ['coupons' => $this->couponRepository->findValidCouponsByCost($total)] : [])
        );
    }

    public function checkout($data)
    {
        $ids = $data['ids'] ?? [];
        $code = $data['code'] ?? null;
        $carts = [];
        foreach ($ids as $id) {
            $cart = $this->cartRepository->findById($id);
            if ($cart) {
                $carts[] = $id;
            } else return false;
        }
        if (count($carts) > 0) {
            Session::put('carts', $carts);
            if ($code) {
                $coupon = $this->couponRepository->findByCode($code);
                if ($coupon) {
                    Session::put('code', $code);
                }
            }
            return APIResponse::make(true, 'success', '', ['link' => route('checkout')]);
        } else {
            return APIResponse::make(false, 'info', 'Bạn chưa chọn khóa học nào');
        }
    }

    public function removeCode()
    {
        if (Session::has('code')) {
            Session::forget('code');
        }
    }

    public function remove($id)
    {
        $cart = $this->cartRepository->findById($id);
        if ($cart) {
            // Gate::authorize('delete', $cart);
            $removeCart = $this->cartRepository->removeFromCart($id);
            if ($removeCart) {
                $count = $this->cartRepository->countCart();
                return APIResponse::make(true, 'success', 'Đã xóa khóa học khỏi giỏ hàng.', $count);
            }
        }
    }
}
