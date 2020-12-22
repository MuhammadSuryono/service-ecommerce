<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Transactions;
use App\Orders;
use App\Customers;
use App\OrderItems;
use App\NotifTable;

use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\Transaction;

use App\Http\Controllers\Midtrans\ApiRequestor;
use App\Http\Controllers\Midtrans\CoreApi;
use App\Http\Controllers\Midtrans\Notification;
use App\Http\Controllers\Midtrans\Snap;
use App\Http\Controllers\Midtrans\SnapApiRequestor;

use App\Http\Controllers\Midtrans\Sanitizer;

class TransactionController extends Controller
{
    private $request;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getAll()
    {
        $data = Transactions::all();
        return $this->BuildResponse(true, "Success retrieve all data transaction", $data, 200);
    }

    public function getById($id)
    {
        $data = Transactions::find($id);
        if(!$data)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> $data], 404);
        }
        Log::info("Get data Transactions $data->full_name");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function create()
    {
        $this->validate($this->request, [
            "order_id" => 'required',
            "payment_metode" => 'required',
        ]);

        $order_id = $this->request->input("order_id");
        $payment = $this->request->input("payment_metode");

        $check = $this->CheckOrder($order_id);
        if (!$check)
        {
            return $this->BuildResponse(false, "Order id notfound!", [], 404);
        }

        $order = new OrderController();
        $dataOrder = $order->GetOrderByOrderId($order_id);

        $transactionId = time()+$order_id;
        $saveTransaction = new Transactions();
        $saveTransaction->order_id = $order_id;
        $saveTransaction->transaction_id = $transactionId;
        $saveTransaction->payment_type = "bank_transfer";
        $saveTransaction->total = $dataOrder->total_price;

        $saveTransaction->save();
        $idTransaction = $saveTransaction->id;

        // {
        //    "status_code": "201",
        //    "status_message": "Success, Bank Transfer transaction is created",
        //    "transaction_id": "68449366-15da-46a5-909b-28150e6ec841",
        //    "order_id": "IOKN-8898999",
        //    "merchant_id": "G101348486",
        //    "gross_amount": "190000.00",
        //    "currency": "IDR",
        //    "payment_type": "bank_transfer",
        //    "transaction_time": "2020-12-21 08:02:53",
        //    "transaction_status": "pending",
        //    "va_numbers": [
        //        {
        //            "bank": "bni",
        //            "va_number": "9884848613556429"
        //        }
        //    ],
        //    "fraud_status": "accept"
        //}

        $body = $this->BodyMidtrans("bank_transfer", array(["gross_amount" => $dataOrder->total_price, "order_id" => $order_id]), array(["bank"=> "bni"]));
        $header = $this->GenerateHeaderMidtrans();

        $req = $this->request_API_POST($body, "https://api.sandbox.midtrans.com/v2/charge", $header);

        if ($req->status_code == "201")
        {
            $dataUpdate = [
              "time_create_payment" => $req->transaction_time,
              "transaction_status" =>  $req->transaction_status,
            ];

            $this->UpdateTransaction($dataUpdate, $order_id);
            return $this->BuildResponse(true, "Transaction success create", "", 200);
        }

    }

    public function notif(Request $request)
    {
        $req = $request->all();
        $pay = Transactions::where('order_id', $req['order_id'])->get();
        // return $pay;
        $pays = Transactions::find($pay[0]->id);
        if(!$pay)
        {
            return response()->json(["messages"=> "Id order not found","status"=>"error"]);
        }
        $pays->transaction_time = $req['transaction_time'];
        $pays->transaction_status = $req['transaction_status'];
        $pays->transaction_id = $req['transaction_id'];
        if($pays->save())
        {
            return response()->json(["messages"=> "Perubahan transaksi"], 200);
        }

    }

    public function UpdateTransaction(array $data, $order_id)
    {
        $update = Orders::where("order_id", $order_id);
        $update->update($data);

        return true;
    }
}
