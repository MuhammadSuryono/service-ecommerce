<?php

namespace App\Http\Controllers;

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
        $data = Orders::all();
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        $data = Orders::find($id);
        if(!$data)
        {
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> $data], 404);
        }
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

        $order = new Orders();
        $order->user_id = $id_user;
        $order->order_id = $order_id;
        $order->total_quantity = $input['total_quantity'];
        $order->total_price = $input['total_price'];
        $order->order_status = "Waiting Payment";
        $order->save();

        return $this->BuildResponse(true, "Success create new order", $this->request->all(), 200);
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

}
