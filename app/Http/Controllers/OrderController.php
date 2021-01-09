<?php

namespace App\Http\Controllers;

use App\Cart;
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

        $order = Orders::where("user_id", $this->request->input('user_id'))->get();
        return $this->BuildResponse(true, "Success get data order", $order, 200);
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
	* 
	* 
	*/
	public function GetOrder($order_id)
	{
		$data = Orders::where("order_id", $order_id)->get();
		
		return $this->BuildResponse(true, "Success get data order", $data, 200);
	}
}
