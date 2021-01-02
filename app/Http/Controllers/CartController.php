<?php


namespace App\Http\Controllers;


use App\Cart;
use Illuminate\Http\Request;


class CartController extends Controller
{
    /***
     * @var Request
     */
    private $request;

    /***
     * CartController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addCart()
    {
        $this->validate($this->request, [
            'id_user' => 'required',
            'id_product' => 'required',
            'quantity' => 'required',
            'item_price' => 'required'
        ]);

        $cart = new Cart();
        $cart->id_user = $this->request->input('id_user');
        $cart->product_id = $this->request->input('id_product');
        $cart->quantity = $this->request->input('quantity');
        $cart->item_price = $this->request->input('item_price');

        $cart->save();
        return $this->BuildResponse(true, "Success add to cart", $this->request->all(), 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeCart($id)
    {
        $cart = Cart::find($id);
        $cart->delete();

        return $this->BuildResponse(true, "Remove product success", $id, 200);
    }

    public function getCartByUser($userId)
    {
        $cart = Cart::where("id_user", $userId)->get();

        return $this->BuildResponse(true, "Cart product success", $userId, 200);
    }
}
