<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OtpController extends \App\Http\Controllers\Controller
{
    public function ValidateOtp(Request $request)
    {
        $this->validate($request, [
           "code_otp" => "required",
            "id_user" => "required",
        ]);

        $check = $this->CheckOtp($request->input("code_otp"));
        if ($check) {
            $user = \App\User::find($request->input("id_user"));
            $user->is_register = true;

            $user->update();

            return $this->BuildResponse(true, "Success Register", $user, 200);
        }
        return $this->BuildResponse(false, "Invalid OTP", null, 400);
    }
}
