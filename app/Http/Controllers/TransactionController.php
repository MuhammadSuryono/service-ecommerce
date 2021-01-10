<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Shipper;
use App\User;
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
	
	public function getByOrderId($orderId)
	{
		$data = Transactions::where("order_id", $orderId)->first();
		$users = User::find($data->user_id);
        $product = OrderItems::join('products', 'order_items.product_id', '=', 'products.id')->select('products.*', 'order_items.quantity as qty')->where('order_items.order_id', $orderId)->get();
		$shipper = Shipper::where("id_order", $orderId)->first();
		$data->user = $users;
		$data->products = $product;
		$data->shipper = $shipper;
		
		return $this->BuildResponse(true, "Retrieve transaction success", $data, 200);
	}

    public function create()
    {
        $this->validate($this->request, [
            "order_id" => 'required',
            "payment_metode" => 'required',
            "receipt_man" => 'required',
        ]);


        $order_id = $this->request->input("order_id");
        $payment = $this->request->input("payment_metode");

        $check = $this->CheckOrder($order_id);
        if (!$check)
        {
            return $this->BuildResponse(false, "Order id notfound!", [], 404);
        }

        $order = new OrderController($this->request);
        $dataOrder = $order->GetOrderByOrderId($order_id);



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

        $body = $this->BodyMidtrans("bank_transfer", ["gross_amount" => $dataOrder->total_price + $this->request->input('receipt_man')['cost'], "order_id" => $order_id], ["bank"=> "bni"]);
        $header = $this->GenerateHeaderMidtrans();

        $req = $this->request_API_POST($body, "https://api.sandbox.midtrans.com/v2/charge", ['Authorization: '.$header]);
//        $response = '{"status_code":"201","status_message":"Success, Bank Transfer transaction is created","transaction_id":"1bfcbb4f-6177-4400-9736-8d5782ef4ff1","order_id":"1608820297","merchant_id":"G101348486","gross_amount":"300.00","currency":"IDR","payment_type":"bank_transfer","transaction_time":"2020-12-24 22:02:57","transaction_status":"pending","va_numbers":[{"bank":"bni","va_number":"9884848686348514"}],"fraud_status":"accept"}';
//        $req = json_decode($response);
        if ($req->status_code == "201")
        {
            $transactionId = time();
            $saveTransaction = new Transactions();
            $saveTransaction->order_id = $order_id;
            $saveTransaction->transaction_id = $transactionId;
            $saveTransaction->payment_type = "bank_transfer";
            $saveTransaction->total = $dataOrder->total_price + $this->request->input('receipt_man')['cost'];
            $saveTransaction->time_create_payment = $req->transaction_time;
            $saveTransaction->transaction_status = $req->transaction_status;
            $saveTransaction->transaction_time = "-";
            $saveTransaction->detail_transactions = json_encode($req);
            $saveTransaction->save();
			Orders::where("order_id", $order_id)->where("order_status", "create_on_transaction")->update(["order_status" =>"waiting_payment"]);
            Cart::where('user_id', $dataOrder->user_id)->where('status', "checkout")->update(["status" => "on_transaction"]);
            $this->InsertReciptMan($this->request->input('receipt_man'));
            return $this->BuildResponse(true, "Transaction success create", ["body" =>$body, "transaction_id" => $transactionId, "va_number" => $req->va_numbers[0]->va_number], 200);
        }

        return $this->BuildResponse(false, "Failed create transactions", $body, 200);

    }

    public function notif(Request $request)
    {
		Log::info(json_encode($request->all()));
        $pay = Transactions::where('order_id', $request->input('order_id'))->first();
        // return $pay;
        if(!$pay)
        {
            return response()->json(["messages"=> "Id order not found","status"=>false], 400);
        }
		if (!$request->input('settlement_time') || $request->input('settlement_time') == "") {
			$pays = Transactions::find($pay->id);
			$pays->transaction_time = $request->input('transaction_time');
			$pays->transaction_status = $request->input('transaction_status');
			if($pays->save())
			{
				return response()->json(["messages"=> "Perubahan transsaksi"], 200);
			}
		}
		
		
        $pays = Transactions::find($pay->id);
        $pays->transaction_time = $request->input('settlement_time');
        $pays->transaction_status = $request->input('transaction_status');
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

    public function InsertReciptMan($data)
    {
        Log::info($data);
        $ship = new Shipper();
        $ship->id_order = $data['order_id'];
        $ship->recipents_name = $data['penerima'];
        $ship->address = $data['address'];
        $ship->contact = $data['contact'];
        $ship->order_notes = $data['notes'];
        $ship->destination = $data['destination'];
        $ship->destination_id = $data['destination_id'];
        $ship->cost = $data['cost'];

        $ship->save();
    }
}
