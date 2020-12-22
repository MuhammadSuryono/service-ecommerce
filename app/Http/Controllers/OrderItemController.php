<?php

namespace App\Http\Controllers;

use App\OrderItems;
use App\Products;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /***
     * @var Request
     */
    private $request;

    /***
     * OrderItemController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /***
     * @param array $data
     * @param string $order_id
     * @return array
     */
    public function createOrderItems(array $data, string $order_id)
    {
        $totalQuantity = 0;
        $totalPrice = 0;
        foreach ($data as $item)
        {
            $idItem = $item['id_item'];
            $itemPrice = Products::where('id', $idItem)->value('price');
            $totalPrice = $totalPrice + ($itemPrice * $item['quantity']);
            $totalQuantity = $totalQuantity + $item['quantity'];

            $saveOrderItem = new OrderItems();
            $saveOrderItem->order_id = $order_id;
            $saveOrderItem->product_id = $idItem;
            $saveOrderItem->quantity = $item['quantity'];
            $saveOrderItem->item_price = $itemPrice;

            $saveOrderItem->save();
        }

        return ["total_quantity" => $totalQuantity, "total_price" => $totalPrice];
    }
}
