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
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;

class TransactionService implements TransactionServiceInterface
{
    private $orderRepository;
    private $cartRepository;
    private $couponRepository;
    private $transactionRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        OrderRepositoryInterface $orderRepository,
        CouponRepositoryInterface $couponRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
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


    public function create($request, $method)
    {
        if ($method === PaymentMethod::VNPAY) {
            $orderId = $request->vnp_TxnRef;
            $statusCode = $request->vnp_ResponseCode == '00' ? true : false;
        } elseif ($method === PaymentMethod::MOMO) {
            $orderId = $request->orderId;
            $statusCode = $request->resultCode == '0' ? true : false;
        }

        $order = $this->orderRepository->find($orderId);
        if ($order) {
            DB::beginTransaction();
            try {
                $this->transactionRepository->create([
                    'user_id' => Auth::user()->id,
                    'payment_method' => $method,
                    'order_id' => $order->id,
                    'response' => json_encode($request->all())
                ]);
                if ($statusCode) {

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
                } else {
                    $order->state = OrderState::FAILED;
                    $order->save();
                }
                DB::commit();
                return $statusCode ? [$method, $order->id] : false;
            } catch (\Exception $ex) {
                DB::rollback();
                Log::error('VNPay transaction failed: ' . $ex->getMessage());
                return false;
            }
        }
        return false;
    }
}
