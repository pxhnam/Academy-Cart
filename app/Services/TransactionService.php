<?php

namespace App\Services;

use App\Enums\CartState;
use App\Enums\OrderState;
use App\Enums\PaymentMethod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;

class TransactionService implements TransactionServiceInterface
{
    private $orderService;
    private $cartRepository;
    private $couponRepository;
    private $transactionRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        OrderServiceInterface $orderService,
        CouponRepositoryInterface $couponRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->orderService = $orderService;
        $this->cartRepository = $cartRepository;
        $this->couponRepository = $couponRepository;
    }
    // public function VNPay($request)
    // {
    //     $vnp_HashSecret = 'F4T9SZ131V6BBHJ18IKOUPZXBXJS1MUY';
    //     $vnp_SecureHash = $request->vnp_SecureHash;
    //     $inputData = $request->except('vnp_SecureHash', 'vnp_SecureHashType');
    //     $hashData = "";
    //     foreach ($inputData as $key => $value) {
    //         $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    //     }
    //     $hashData = ltrim($hashData, '&');
    //     $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    //     if ($secureHash == $vnp_SecureHash) {
    //         $order = $this->orderService->find($request->vnp_TxnRef);
    //         // Validate successful
    //         if ($request->vnp_ResponseCode == '00') {
    //             // Payment success
    //             DB::transaction(function () use ($order) {
    //                 $this->transactionRepository->create([
    //                     'user_id' => Auth::user()->id,
    //                     'bank_account' => '123456789',
    //                     'bank_name' => 'Test',
    //                     'card_expiry_date' => Carbon::now(),
    //                     'payment_method' => PaymentMethod::VNPay,
    //                     'order_id' => $order->id,
    //                 ]);
    //                 $order->state = OrderState::PAID;
    //                 $order->save();
    //                 $ids = Session::get('carts');
    //                 foreach ($ids as $id) {
    //                     $cart = $this->cartRepository->findById($id);
    //                     if ($cart) {
    //                         $cart->state = CartState::PURCHASED;
    //                         $cart->save();
    //                     }
    //                 }
    //                 Session::forget(['carts', 'code']);
    //             });

    //             return $order->id;
    //         } else {
    //             // Payment fail
    //             $order->state = OrderState::FAILED;
    //             $order->save();
    //             return false;
    //         }
    //     } else {
    //         return false;
    //     }
    // }
    public function VNPay($request)
    {
        $order = $this->orderService->find($request->vnp_TxnRef);
        if ($order) {
            DB::beginTransaction();
            try {
                $this->transactionRepository->create([
                    'user_id' => Auth::user()->id,
                    'payment_method' => PaymentMethod::VNPay,
                    'order_id' => $order->id,
                    'response' => json_encode($request->all())
                ]);
                $order->state = OrderState::PAID;
                $order->save();
                $ids = Session::get('carts');
                foreach ($ids as $id) {
                    $cart = $this->cartRepository->findById($id);
                    if ($cart) {
                        $cart->state = CartState::PURCHASED;
                        $cart->save();
                    }
                }
                if (Session::has('codes')) {
                    $codes = Session::get('codes');
                    foreach ($codes as $code) {
                        $coupon = $this->couponRepository->findByCode($code);
                        if ($coupon) {
                            $coupon->usage_count++;
                            $coupon->save();
                        }
                    }
                }
                Session::forget(['carts', 'codes']);
                DB::commit();
                return $order->id;
            } catch (\Exception $ex) {
                DB::rollback();
                Log::error('VNPay transaction failed: ' . $ex->getMessage());
                return false;
            }
        }
        return false;
    }
}
