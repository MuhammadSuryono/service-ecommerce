<?php


namespace App\Http\Controllers;


use App\Category;
use App\Orders;
use App\Products;
use App\Transactions;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\Api;

class ReportController extends Controller
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

    public function getReport()
    {
        $product = Products::all();
        $category = Category::all();
        $order = Orders::where("order_status", "checkout")->get();
        $transactions = Transactions::where("transaction_status", "settlement")->get();

        return $this->BuildResponse(true, "Success retrieve data", [
            "products" => count($product),
            "category" => count($category),
            "order" => count($order),
            "transaction" => count($transactions)
        ], 200);
    }
}
