<?php

namespace App\Http\Controllers;

use App\Otp;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Customers;
use App\Orders;

class CustomerController extends Controller
{
    /***
     * CustomerController constructor.
     */
    public function __construct()
    {

    }

    /***
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $data = User::all();
        return $this->BuildResponse(true, "success retrieve data", $data, 200);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById(Request $request, $id)
    {
        $data = User::find($id);
        if(!$data)
        {
            return $this->BuildResponse(false, "Failed retrieve data", [], 404);
        }
        return $this->BuildResponse(true, "Success retrieve data", $data, 200);

    }

    /***
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $this->validate($request, [
           'fullname' => 'required',
           'email' => 'required',
           'npwp' => 'required',
           'username' => 'required',
           'password' => 'required',
        ]);

        $email = User::where("email", $request->input("email"));
        if ($email->exists()) return $this->BuildResponse(false, "Email is register!", $request->all(), 400);

        $customer = new User();
        $customer->fullname = $request->input('fullname');
        $customer->no_npwp = $request->input('npwp');
        $customer->address = "-";
        $customer->email = $request->input('email');
        $customer->phone_number = "0";
        $customer->username = $request->input('username');
        $customer->password = $request->input('password');

        if ($customer->save())
        {
            $request->request->add(["id_user" => $customer->id]);

            $codeOtp = $this->CreateOtp();

            $this->SendEmail($request->input('email'), $request->input('fullname'), $codeOtp);

            return $this->BuildResponse(true, "Success register!", $request->all(), 200);
        }

        return $this->BuildResponse(false, "Failed register!", $request->all(), 400);
    }

    /***
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'fullname' => 'required',
            'email' => 'required',
            'npwp' => 'required',
            'address' => 'required',
            'nohp' => 'required|int',
        ]);

        $customer = User::find($id);

        if(!$customer)
        {
            return $this->BuildResponse(false, "User not found!", $customer, 404);
        }
        $customer->fullname = $request->input('full_name');
        $customer->npwp = $request->input('npwp');
        $customer->address = $request->input('address');
        $customer->email = $request->input('email');
        $customer->phone_number = $request->input('nohp');
        $customer->username = $request->input('username');
        $customer->password = $request->input('password');
        $customer->ktp = $request->input('ktp');

        if ($customer->save())
        {
            return $this->BuildResponse(true, "Success update data user!", [], 200);
        }

        return $this->BuildResponse(false, "Failed update data user!", [], 400);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $data = User::find($id);
        if(!$data)
        {
            return $this->BuildResponse(false, "User not found!", $data, 404);
        }

        if($data->delete())
        {
            return $this->BuildResponse(true, "Success delete data user!", [], 200);
        }
        return $this->BuildResponse(false, "Failed delete data user!", [], 400);
    }
}
