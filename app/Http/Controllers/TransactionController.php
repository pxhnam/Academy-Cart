<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\MomoServiceInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\VNPayServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;

class TransactionController extends Controller
{

    private $transactionService;
    private $vnpayService;
    private $momoService;

    public function __construct(
        TransactionServiceInterface $transactionService,
        VNPayServiceInterface $vnpayService,
        MomoServiceInterface $momoService,
    ) {
        $this->transactionService = $transactionService;
        $this->vnpayService = $vnpayService;
        $this->momoService = $momoService;
    }


    public function vnpayReturn(Request $request)
    {
        $data = $this->vnpayService->response($request);
        if (is_int($data)) {
            dd($data);
            //success
            // return redirect()->route('checkout');
        } else {
            // dd($data);
            return redirect()->route('checkout')->with([
                'notify' =>
                [
                    'type' => 'error',
                    'message' => 'Thanh toán thất bại.'
                ]
            ]);
        }
    }

    public function momoReturn(Request $request)
    {
        $data = $this->momoService->response($request);
        if (is_int($data)) {
            dd($data);
            //success
            // return redirect()->route('checkout');
        } else {
            // dd($data);
            return redirect()->route('checkout')->with([
                'notify' =>
                [
                    'type' => 'error',
                    'message' => 'Thanh toán thất bại.'
                ]
            ]);
        }
    }
}
