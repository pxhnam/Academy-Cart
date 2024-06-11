<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\VNPayServiceInterface;

class OrderController extends Controller
{

    private $orderService;
    private $vnpayService;


    public function __construct(
        OrderServiceInterface $orderService,
        VNPayServiceInterface $vnpayService
    ) {
        $this->orderService = $orderService;
        $this->vnpayService = $vnpayService;
    }

    public function index()
    {
        return view('client.home.checkout', $this->orderService->show());
    }
    public function checkout(Request $request)
    {
        $method = $request->method;
        if ($method) {
            switch ($method) {
                case PaymentMethod::VNPay: {
                        $order = $this->orderService->createOrder();
                        if ($order) {
                            $request->merge([
                                'order_id' => $order['order_id'],
                                'total' => $order['total']
                            ]);
                            $vnp_Url = $this->vnpayService->processPayment($request);
                            return redirect($vnp_Url);
                        }
                        return redirect()->back()->with(
                            ['notify' => ['type' => 'error', 'message' => 'Có lỗi xảy ra! Vui lòng thử lại sau.']]
                        );
                    }
                case PaymentMethod::Momo:
                    return redirect()->back()->with(
                        ['notify' => ['type' => 'info', 'message' => PaymentMethod::Momo . ' chưa được hỗ trợ.']]
                    );
                case PaymentMethod::ZaloPay:
                    return redirect()->back()->with(
                        ['notify' => ['type' => 'info', 'message' => PaymentMethod::ZaloPay . ' chưa được hỗ trợ.']]
                    );
                default:
                    return redirect()->back()->withErrors(['method' => 'Không rõ phương thức thanh toán.']);
            }
        } else {
            return redirect()->back()->withErrors(['method' => 'Vui lòng chọn phương thức thanh toán.']);
        }
    }
}
