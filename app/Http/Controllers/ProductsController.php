<?php

namespace App\Http\Controllers;

use App\Http\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Products;
use App\Orders;

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
        $data = Products::join('categories', 'products.category_id', '=', 'categories.id')->get();
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
        $this->validate($this->request,
        [
            'item_name' => 'required',
            'item_code' => 'required',
            'category_id' => 'required',
            'stock' => 'required',
        ]);

        $check = Products::where('item_code');
        if ($check->exists())
        {
            return $this->BuildResponse(false, "Item code is exists", $check->first(), 400);
        }

        $image = $this->request->file('image');

        $product = new Products();
        $product->item_name = $this->request->input('item_name');
        $product->item_code = $this->request->input('item_code');
        $product->category_id = $this->request->input('category_id');
        $product->stock = $this->request->input('stock');
        $product->color = $this->request->input('color');
        $product->item_size = $this->request->input('size');
        $product->description = $this->request->input('description');
        $product->image = '-';

        if (!empty($image))
        {
            $imageName = time()."-".$image->getClientOriginalName();
            $destination = storage_path('/app/images');
            $image->move($destination, $imageName);
            $product->image = $imageName;
        }

        $product->save();
        return $this->BuildResponse(true, "Create product is success", $this->request->all(), 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update()
    {
        $this->validate($this->request, [
            'id' => 'required',
            'item_name' => 'required',
            'category_id' => 'required',
            'stock' => 'required',
            'selection_stock' => 'required',
        ]);

        $id = $this->request->input('id');
        $product = Products::find($id);
        if(!$product)
        {
            Log::error("Data not found");
            return response()->json(["message"=>"failed retrieve data","status" => false,"data"=> ''], 404);
        }

        $stock = $this->request->input('stock');
        if ($this->request->input('selection_stock') == 'penambahan')
        {
            $stock = $product->stock + $stock;
        }
        $product->item_name = $this->request->input('item_name');
        $product->category_id = $this->request->input('category_id');
        $product->stock = $stock;
        $product->color = $this->request->input('color');
        $product->item_size = $this->request->input('size');
        $product->description = $this->request->input('description');

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
