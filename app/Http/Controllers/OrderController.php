<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\MomoServiceInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\VNPayServiceInterface;

class OrderController extends Controller
{

    private $orderService;
    private $vnpayService;
    private $momoService;


    public function __construct(
        OrderServiceInterface $orderService,
        VNPayServiceInterface $vnpayService,
        MomoServiceInterface $momoService
    ) {
        $this->orderService = $orderService;
        $this->vnpayService = $vnpayService;
        $this->momoService = $momoService;
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
                case PaymentMethod::QR_VNPay:
                    return redirect()->back()->with(
                        ['notify' => ['type' => 'info', 'message' => PaymentMethod::QR_VNPay . ' chưa được hỗ trợ.']]
                    );
                case PaymentMethod::QR_Momo:

                    $response = $this->momoService->createQR($request);
                    if ($response['errorCode'] == 0) {
                        dd($response['qrCodeUrl']);
                        return response()->json(['qrCodeUrl' => $response['qrCodeUrl']]);
                    } else {
                        return response()->json(['error' => $response['localMessage']], 400);
                    }
                    // return redirect()->back()->with(
                    //     ['notify' => ['type' => 'info', 'message' => PaymentMethod::QR_Momo . ' chưa được hỗ trợ.']]
                    // );
                default:
                    return redirect()->back()->withErrors(['method' => 'Không rõ phương thức thanh toán.']);
            }
        } else {
            return redirect()->back()->withErrors(['method' => 'Vui lòng chọn phương thức thanh toán.']);
        }
    }
}
