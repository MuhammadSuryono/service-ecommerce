<?php

namespace App\Http\Controllers;

use App\Orders;
use App\User;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private $JWT_SECRET = 'CV-BIG';
    private $SERVER_KEY = "SB-Mid-server-jMa1yoEHLCbuNPkScwv9LKwI";
    protected $API_KEY_RAJA_ONGKIR = "8f832337b09d6a1449dbe6044d90fd3f";

    /***
     * @param bool $status
     * @param string $message
     * @param mixed $data
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function BuildResponse(bool $status, string $message, $data, int $status_code = 200)
    {
        $response = [
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ];
        return response()->json($response, $status_code);
    }

    /***
     * @return string
     */
    public function JWT_SCRET ()
    {
        return $this->JWT_SECRET;
    }

    /***
     * @param $id_user
     * @return bool
     */
    public function CheckUser($id_user)
    {
        $check = User::where('id', $id_user);
        if ($check)
        {
            return true;
        }

        return false;
    }

    /***
     * @param $id_user
     * @return int
     */
    public function GenerateOrderId($id_user)
    {
        return time()+$id_user;
    }

    /***
     * @param $order_id
     * @return bool
     */
    public function CheckOrder($order_id)
    {
        $check = Orders::where('order_id', $order_id);
        if ($check->exists())
        {
            return true;
        }

        return false;
    }

    /***
     * @return string
     */
    public function GenerateHeaderMidtrans()
    {
        $enc = base64_encode($this->SERVER_KEY.":");
        $header = "Basic ".$enc;

        return $header;
    }

    /***
     * @param array $body
     * @param $url
     * @param array $header
     * @return mixed
     */
    public function request_API_POST($body = [], $url, $header = []) {
        $headr = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];


        if(!empty($header)) $headr = array_merge($headr, $header);

        $crl = curl_init();

        curl_setopt_array($crl, array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_ENCODING       => "",     // handle compressed
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 60,    // time-out on connect
            CURLOPT_TIMEOUT        => 60,    // time-out on response
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headr,
        ));


        $result = curl_exec($crl);
        $error = curl_error($crl);

        curl_close($crl);

        Log::info($url);
        Log::info($header);
        Log::info($body);
        Log::info($error);
        Log::info($result);
        return json_decode($result);
    }

    public function http_request_get(string $url, $body=[], $header=[])
    {
        $headr = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];


        if(!empty($header)) $headr = array_merge($headr, $header);

        $crl = curl_init();

        curl_setopt_array($crl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headr,
        ));


        $result = curl_exec($crl);
        $error = curl_error($crl);

        curl_close($crl);

        Log::info($url);
        Log::info("HEADER");
        Log::info($headr);
        Log::info($body);
        Log::info($error);
        Log::info($result);
        return json_decode($result);
    }

    /***
     * @param string $payment_type
     * @param array $transaction_details
     * @param array $bank_transfer
     * @return array
     */
    public function BodyMidtrans(string $payment_type, array $transaction_details, array $bank_transfer)
    {
        $body = [
            "payment_type" => "bank_transfer",
            "transaction_details" => $transaction_details,
            "bank_transfer" => $bank_transfer
        ];
        return $body;
    }

    /***
     * @return string[]
     */
    public function ListBank()
    {
        return ["bni", "bca", "permata"];
    }

    /***
     * @return string
     */
    public function GetKeyRajaOngkir()
    {
        return $this->API_KEY_RAJA_ONGKIR;
    }

    /***
     * @return string
     */
    public function BaseUrlRajaOngkir()
    {
        return "https://api.rajaongkir.com/starter/";
    }

}
