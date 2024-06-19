<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Http\Controllers\Controller;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
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
                    $response = $this->momoService->create($request);
                    // dd($response);
                    return redirect($response['payUrl']);
                    // Tạo mã QR từ URL


                    // $qrcode = new QRCode();
                    // $image = $qrcode->render($response['qrCodeUrl']);
                    // $image = base64_encode($image);
                    // dd($image);
                    // dd(base64_encode($image));
                    // return view('welcome', compact('image'));
                    // return redirect()->back()->with(
                    //     ['notify' => ['type' => 'info', 'message' => PaymentMethod::MOMO . ' chưa được hỗ trợ.']]
                    // );
                case PaymentMethod::BANK:

                    $response = $this->momoService->create($request);
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
