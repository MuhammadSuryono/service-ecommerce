<?php


namespace App\Http\Controllers;


use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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
		
		$check = Cart::where('product_id', $this->request->input('id_product'))->where('user_id', $this->request->input('id_user'))->where('status', 'cart');
		if ($check->exists()) {
			$data = ["quantity" => $this->request->input('quantity') + $check->first()->quantity ];
			$check->update($data);
			return $this->BuildResponse(true, "Success update quantity to cart " ,  $this->request->all(), 200);
		}

        $cart = new Cart();
        $cart->user_id = $this->request->input('id_user');
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

    /***
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartByUser($userId)
    {
        $cart = Cart::join('products', 'cart.product_id', '=', 'products.id')->select(DB::raw('cart.*, products.item_name as item_name, products.item_code as item_code, products.image as image, products.price as price'))->where("cart.user_id", $userId)->where("cart.status", 'cart')->get();

        return $this->BuildResponse(true, "Cart product success", $cart, 200);
    }

    /***
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartByCheckout($userId)
    {
        $cart = Cart::join('products', 'cart.product_id', '=', 'products.id')->select(DB::raw('cart.*, products.item_name as item_name, products.item_code as item_code, products.image as image, products.price as price, products.weight as weight'))->where("cart.user_id", $userId)->where("cart.status", 'checkout')->get();

        return $this->BuildResponse(true, "Cart product success", $cart, 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateQty($id)
    {
        $cart = Cart::find($id);
        $item_price = $cart->item_price;
        $cart->quantity = $this->request->input('quantity');
		
		Log::info($this->request->input('quantity'));
		
        if ($cart->update()){
			Log::info("SUcces di tambahkan");
            $userId = $this->request->input('id_user');
            $totalUpdate = $item_price*$this->request->input('quantity');
            $grandTotal = Cart::select(DB::raw('sum(cart.quantity) as totalQuantity, sum(cart.item_price) as totalPrice'))->where("cart.user_id", $userId)->where("cart.status", 'cart')->first();
            return $this->BuildResponse(true, "Cart product update success", ["total" => $totalUpdate, "grand_total" => $grandTotal], 200);
        }
        return $this->BuildResponse(false, "Cart product update failed", [], 200);
    }
}
