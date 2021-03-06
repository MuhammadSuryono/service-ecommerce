<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Products;
use App\Orders;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /***
     * @var Request
     */
    private $request;

    /***
     * ProductController constructor.
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
        $data = Products::join('categories', 'products.category_id', '=', 'categories.id')->select(DB::raw('products.*, categories.category_name'))->get();
        return $this->BuildResponse(true, "success retrieve data", $data, 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        $data = Products::join('categories', 'products.category_id', '=', 'categories.id')->where('products.id',$id)->first();
        if(!$data)
        {
            return $this->BuildResponse(false, "Failed retrieve data", $data, 404);
        }
        return $this->BuildResponse(true, "success retrieve data", $data, 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getProductByCategory()
    {
        $this->validate($this->request, [
           'category_id' => 'required',
        ]);

        $category = Category::find($this->request->input('category_id'));
        $data = Products::where('category_id', $this->request->input('category_id'));
        return $this->BuildResponse(true, "Success retrieve data", ["category" => $category, "product" => $data->get()], 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function insert()
    {
        Log::info(json_encode($this->request->all()));
        $this->validate($this->request,
        [
            'item_name' => 'required',
            'item_code' => 'required',
            'category_id' => 'required',
            'stock' => 'required',
            'price' => 'required',
            'unit' => 'required',
            'weight' => 'required',
        ]);

        $check = Products::where('item_code', $this->request->input('item_code'));
        if ($check->exists())
        {
            return $this->BuildResponse(false, "Item code is exists", $check->first(), 400);
        }

        $product = new Products();
        $product->item_name = $this->request->input('item_name');
        $product->item_code = $this->request->input('item_code');
        $product->category_id = $this->request->input('category_id');
        $product->stock = $this->request->input('stock');
        $product->color = $this->request->input('color');
        $product->unit = $this->request->input('unit');
        $product->weight = $this->request->input('weight');
        $product->price = $this->request->input('price');
        $product->description = $this->request->input('description');
        $product->image = $this->request->input('images');

        $product->save();
        return $this->BuildResponse(true, "Create product is success", $this->request->all(), 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update($id)
    {
        $product = Products::find($id);
        if(!$product)
        {
            Log::error("Data not found");
            return response()->json(["message"=>"failed retrieve data","status" => false,"data"=> ''], 404);
        }

        $product->item_name = $this->request->input('item_name');
        $product->item_code = $this->request->input('item_code');
        $product->category_id = $this->request->input('category_id');
        $product->stock = $this->request->input('stock');
        $product->color = $this->request->input('color');
        $product->unit = $this->request->input('unit');
        $product->weight = $this->request->input('weight');
        $product->price = $this->request->input('price');
        $product->description = $this->request->input('description');
        if ($this->request->input('images') != null || $this->request->input('images') != "") $product->image = $this->request->input('images');

        $product->save();
        return $this->BuildResponse(true, "Update product is success", $this->request->all(), 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $product = Products::find($id);
        if(!$product)
        {
            return response()->json(["message"=>"failed retrieve data","status" => false,"data"=> ''], 404);
        }

        $product->delete();
        return response()->json(["message"=>"success delete data","status" => true,"data"=> $product], 200);
    }
}
