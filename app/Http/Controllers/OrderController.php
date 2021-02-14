<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Transactions;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Customers;
use App\OrderItems;
use App\Orders;
use App\Products;

class OrderController extends Controller
{
    /***
     * @var
     */
    private $request;

    /***
     * OrderController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $data = Orders::join('users', 'orders.user_id', '=', 'users.id')->select('orders.*', 'users.fullname')->get();
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        $order = Orders::find($id);
        $users = User::find($order->user_id);
        $product = OrderItems::join('products', 'order_items.product_id', '=', 'products.id')->select('products.*')->where('order_items.order_id', $order->order_id)->get();
        $data = ["order" => $order, "user" => $users, "product" => $product];
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function insert()
    {
        $this->validate($this->request, [
           'data' => 'required',
            'user_id' => 'required'
        ]);

        $check = $this->CheckUser($this->request->input('user_id'));
        if (!$check)
        {
            return $this->BuildResponse(false, "User not found!", $this->request->input('user_id'), 400);
        }
        $id_user = $this->request->input('user_id');
        $order_id = $this->GenerateOrderId($id_user);

        if ($this->CheckOrder($order_id))
        {
            return $this->BuildResponse(false, "Order id is exists", $order_id, 400);
        }

        $items = new OrderItemController($this->request);
        $input = $items->createOrderItems($this->request->input('data'), $order_id);
        if (!$input['status']) return $this->BuildResponse(false, "Error when cerate new order", $this->request->all(), 400);
        $order = new Orders();
        $order->user_id = $id_user;
        $order->order_id = $order_id;
        $order->total_quantity = $input['total_quantity'];
        $order->total_price = $input['total_price'];
        $order->order_status = "create_on_transaction";
        $order->save();

        Cart::where('user_id', $id_user)->where("status", "cart")->update(["status" => "checkout"]);

        return $this->BuildResponse(true, "Success create new order", ["order_id" => $order_id, "data" => $this->request->all()], 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function GetOrderByUserId()
    {
        $this->validate($this->request, [
            'user_id' => 'required'
        ]);

        $user = $this->CheckUser($this->request->input('user_id'));
        if (!$user)
        {
            return $this->BuildResponse(false, "User notfound!", [], 404);
        }

        $orders = Orders::where("user_id", $this->request->input('user_id'))->get();
        foreach ($orders as $order) {
            $product = OrderItems::join('products', 'order_items.product_id', '=', 'products.id')->select('products.item_code','products.item_name', 'order_items.item_price', 'order_items.quantity', 'products.image')->where('order_items.order_id', $order->order_id)->get();
            $order->product_orders = $product;

            $transaction = Transactions::join('shipper', 'transactions.order_id', '=', 'shipper.id_order')
                ->select('transactions.transaction_id', 'transactions.payment_type', 'transactions.status_pengiriman', 'transactions.number_resi','transactions.time_create_payment', 'transactions.transaction_status', 'transactions.detail_transactions', 'shipper.*')
                ->where('transactions.order_id', $order->order_id)
                ->first();
            $order->transactions = $transaction;
        }

        return $this->BuildResponse(true, "Success get data order", $orders, 200);
    }

    /***
     * @param $order_id
     * @return mixed
     */
    public function GetOrderByOrderId($order_id)
    {
        return Orders::where("order_id", $order_id)->first();
    }

    /***
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */
	public function GetOrder($orderId)
	{
		$data = Orders::where("order_id", $orderId)->first();
        $users = User::find($data->user_id);
        $product = OrderItems::join('products', 'order_items.product_id', '=', 'products.id')->select('products.*', 'order_items.quantity as qty')->where('order_items.order_id', $orderId)->get();
        $transactionDetails = Transactions::join('shipper', 'transactions.order_id', '=', 'shipper.id_order')
            ->select('transactions.transaction_id', 'transactions.payment_type','transactions.status_pengiriman','transactions.number_resi', 'transactions.time_create_payment', 'transactions.transaction_status', 'transactions.detail_transactions', 'shipper.*')
            ->where('transactions.order_id', $orderId)
            ->first();

        $data = ["order" => $data, "user" => $users, "product" => $product, "transactions" => $transactionDetails];
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);

		return $this->BuildResponse(true, "Success get data order", $data, 200);
	}

	public function CancelOrder($orderId)
    {
        $order = Orders::where("order_id", $orderId)->first();
        $update = Orders::where("order_id", $orderId)->where("order_status", $order->order_status)->update(["order_status" =>"cancel_order"]);
        $updateTransaksi = Transactions::where("order_id", $orderId);
        if ($update && $updateTransaksi->exists()) {
            $updateTransaksi->update(["transaction_status" =>"cancel_order"]);
            return $this->BuildResponse(true, "Cancel order success", $order, 200);
        }

        if ($update) {
            return $this->BuildResponse(true, "Cancel order success", $order, 200);
        }
        return $this->BuildResponse(false, "Cancel order failed", $order, 400);
    }

    public function ReceivedOrder($orderId)
    {
        $transaction = Transactions::where("order_id", $orderId)->first();

        $update = Transactions::find($transaction->id);
        $update->status_pengiriman = "received";
        $update->save();
        return $this->BuildResponse(true, "Success update status pengiriman", $update, 200);
    }
}
