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
                case PaymentMethod::VNPAY: {
                        $order = $this->orderService->createOrder();
                        if ($order) {
                            $request->merge([
                                'order_id' => $order['order_id'],
                                'total' => $order['total']
                            ]);
                            $vnp_Url = $this->vnpayService->create($request);
                            return redirect($vnp_Url);
                        }
                        return redirect()->back()->with(
                            ['notify' => ['type' => 'error', 'message' => 'Có lỗi xảy ra! Vui lòng thử lại sau.']]
                        );
                    }
                case PaymentMethod::MOMO:
                    $order = $this->orderService->createOrder();
                    if ($order) {
                        $request->merge([
                            'order_id' => $order['order_id'],
                            'total' => $order['total']
                        ]);
                        $response = $this->momoService->create($request);
                        return redirect($response['payUrl']);
                    }
                    return redirect()->back()->with(
                        ['notify' => ['type' => 'error', 'message' => 'Có lỗi xảy ra! Vui lòng thử lại sau.']]
                    );
                case PaymentMethod::BANK:
                    $stk = '1042806691';
                    $bank = 'VCB';
                    $order = $this->orderService->createOrder();
                    if ($order) {
                        $qrPay = 'https://img.vietqr.io/image/' . $bank . '-' . $stk . '-compact.png?' .
                            'amount=' .
                            $order['total'] .
                            '&addInfo=' .
                            'Thanh toán cho mã đơn hàng: #' .
                            $order['order_id'];
                        return response()->json(['success' => true, 'qrPay' => $qrPay]);
                    }

                    return response()->json(['success' => false, 'message' => 'Không lấy được mã qr']);
                default:
                    return redirect()->back()->withErrors(['method' => 'Không rõ phương thức thanh toán.']);
            }
        } else {
            return redirect()->back()->withErrors(['method' => 'Vui lòng chọn phương thức thanh toán.']);
        }
    }

    public function result()
    {
        // session()->put('result', ['MOMO', 31]);
        if (session()->has('result')) {
            $data = session('result');
            if (is_array($data)) {
                list($method, $orderId) = $data;
                $data = $this->orderService->bill($orderId);
                $data['method'] = $method;
            }

            return view('client.home.result', compact('data'));
        } else {
            return redirect()->route('home');
        }
    }
}
