<?php

namespace App\Services;

use App\Services\Interfaces\MomoServiceInterface;
use Exception;

class MomoService implements MomoServiceInterface
{

    protected $endpoint;
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $returnUrl;
    public function __construct()
    {
        $this->endpoint = env('MOMO_ENDPOINT');
        $this->partnerCode = env('MOMO_PARTNER_CODE');
        $this->accessKey = env('MOMO_ACCESS_KEY');
        $this->secretKey = env('MOMO_SECRET_KEY');
        $this->returnUrl = env('MOMO_RETURN_URL');
    }


    function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
    public function create($request)
    {

        $partnerCode = $this->partnerCode;
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;
        $orderInfo = "Thanh toán qua MoMo";
        $amount = "100000";
        $orderId = time() . "";
        $redirectUrl = $this->returnUrl;
        $ipnUrl = $this->returnUrl;
        $extraData = "";

        // $partnerCode = 'MOMOBKUN20180529';
        // $accessKey = 'klm05TvNBzhg7h7j';
        // $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        // $orderInfo = "Thanh toán qua MoMo";
        // $amount = "100000";
        // $orderId = time() . "";
        // $redirectUrl = "http://127.0.0.1:8000/momo-return";
        // $ipnUrl = "http://127.0.0.1:8000/momo-return";
        // $extraData = "";

        // if (!empty($_POST)) {
        // $partnerCode = $_POST["partnerCode"];
        // $accessKey = $_POST["accessKey"];
        // $serectkey = $_POST["secretKey"];
        // $orderId = $_POST["orderId"]; // Mã đơn hàng
        // $orderInfo = $_POST["orderInfo"];
        // $amount = $_POST["amount"];
        // $ipnUrl = $_POST["ipnUrl"];
        // $redirectUrl = $_POST["redirectUrl"];
        // $extraData = $_POST["extraData"];

        $requestId = time() . "";
        $requestType = "captureWallet";
        // $extraData = ("");
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "MOMO",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($this->endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json

        // dd($jsonResult['qrCodeUrl']);
        return $jsonResult;
        // header('Location: ' . $jsonResult['payUrl']);
        // }
    }

    public function response($request)
    {

        // $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; //Put your secret key in there
        // $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = $this->secretKey; //Put your secret key in there
        $accessKey = $this->accessKey;

        // if (!empty($_GET)) {
        $partnerCode = $_GET["partnerCode"];
        $orderId = $_GET["orderId"];
        $requestId = $_GET["requestId"];
        $amount = $_GET["amount"];
        $orderInfo = $_GET["orderInfo"];
        $orderType = $_GET["orderType"];
        $transId = $_GET["transId"];
        $resultCode = $_GET["resultCode"];
        $message = $_GET["message"];
        $payType = $_GET["payType"];
        $responseTime = $_GET["responseTime"];
        $extraData = $_GET["extraData"];
        $m2signature = $_GET["signature"]; //MoMo signature


        //Checksum
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
            "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime .
            "&resultCode=" . $resultCode . "&transId=" . $transId;

        $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

        // echo "<script>console.log('Debug huhu Objects: " . $rawHash . "' );</script>";
        // echo "<script>console.log('Debug huhu Objects: " . $partnerSignature . "' );</script>";


        if ($m2signature == $partnerSignature) {
            if ($resultCode == '0') {
                dd('true');
                // $result = '<div class="alert alert-success"><strong>Payment status: </strong>Success</div>';
            } else {
                dd($request->all());
                // $result = '<div class="alert alert-danger"><strong>Payment status: </strong>' . $message . '/' . $localMessage . '</div>';
            }
        } else {
            dd($request->all());
            // dd('cuc');
            // $result = '<div class="alert alert-danger">This transaction could be hacked, please check your signature and returned signature</div>';
        }
        // }
    }
}
