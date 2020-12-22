<?php


namespace App\Http\Controllers;


use App\Http\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /***
     * @var
     */
    private $request;

    /***
     * CategoryContoller constructor.
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
        $category = Category::all();
        return $this->BuildResponse(true, "Success retrieve data", $category, 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create()
    {
        $this->validate($this->request, [
           'category_name' => 'required',
        ]);

        $category = Category::where('category_name', $this->request->input('category_name'));
        if ($category->exists())
        {
            return $this->BuildResponse(false, "Category name is exists", $category->first(), 400);
        }

        $category = new Category();
        $category->category_name = $this->request->input('category_name');
        $category->save();

        return $this->BuildResponse(true, "Create category success", $this->request->input('category_name'), 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update()
    {
        $this->validate($this->request, [
            'id' => 'required',
            'category_name' => 'required',
        ]);

        $id = $this->request->input('id');
        $category_name = $this->request->input('category_name');
        $category = Category::find($id);

        if (!$category)
        {
            return $this->BuildResponse(false, "Category notfound!", $category, 404);
        }

        $category->category_name = $category_name;
        $category->save();
        return $this->BuildResponse(true, "Update category success!", $category, 200);
    }

    /***
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete()
    {
        $this->validate($this->request, [
            'id' => 'required',
        ]);

        $id = $this->request->input('id');
        $category = Category::find($id);

        if (!$category)
        {
            return $this->BuildResponse(false, "Category notfound!", $category, 404);
        }

        $category->delete();
        return $this->BuildResponse(true, "Delete category success!", $category, 200);
    }
}
