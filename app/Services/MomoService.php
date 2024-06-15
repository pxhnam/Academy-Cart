<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Services\Interfaces\MomoServiceInterface;

class MomoService implements MomoServiceInterface
{

    protected $endpoint;
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    public function __construct()
    {
        $this->endpoint = env('MOMO_ENDPOINT');
        $this->partnerCode = env('MOMO_PARTNER_CODE');
        $this->accessKey = env('MOMO_ACCESS_KEY');
        $this->secretKey = env('MOMO_SECRET_KEY');
    }

    public function createQR($request)
    {
        $requestId = time() . "";
        $requestType = "captureWallet";
        $extraData = "";

        // Tạo signature
        $rawHash = "partnerCode=" . $this->partnerCode . "&accessKey=" . $this->accessKey . "&requestId=" . $requestId . "&amount=" . 12000 . "&orderId=" . 17 . "&orderInfo=" . 'don hang ne' . "&returnUrl=" . 'http://127.0.0.1:8000/' . "&notifyUrl=" . 'http://127.0.0.1:8000/' . "&extraData=" . $extraData;
        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        // Tạo request body
        $data = [
            'partnerCode' => $this->partnerCode,
            'accessKey' => $this->accessKey,
            'requestId' => $requestId,
            'amount' => 12000,
            'orderId' => 17,
            'orderInfo' => 'don hang ne',
            'returnUrl' => 'http://127.0.0.1:8000/',
            'notifyUrl' => 'http://127.0.0.1:8000/',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        $client = new Client();
        $response = $client->post($this->endpoint, [
            'json' => $data
        ]);

        return json_decode($response->getBody(), true);
    }
    public function finishedPayment($request)
    {
    }
}
