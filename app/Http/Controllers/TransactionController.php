<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\VNPayServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;

class TransactionController extends Controller
{

    private $transactionService;
    private $vnpayService;
    private $orderService;

    public function __construct(
        TransactionServiceInterface $transactionService,
        OrderServiceInterface $orderService,
        VNPayServiceInterface $vnpayService,
    ) {
        $this->transactionService = $transactionService;
        $this->orderService = $orderService;
        $this->vnpayService = $vnpayService;
    }


    public function vnpayReturn(Request $request)
    {
        $data = $this->vnpayService->finishedPayment($request);
        if (is_int($data)) {
            dd($data);
            //success
            // return redirect()->route('checkout');
        } else {
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
