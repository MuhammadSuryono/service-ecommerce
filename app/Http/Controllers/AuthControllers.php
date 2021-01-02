<?php


namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;

class AuthControllers extends Controller
{
    private $request;

    /***
     * AuthControllers constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /***
     * @param User $user
     * @return string
     */
    protected function jwt(User $user)
    {
        $payload = [
            'iss' => "ecommerce-jwt",
            'sub' => $user->email,
            'iat' => time(),
            'exp' => time() + 60*60,
        ];

        return JWT::encode($payload, $this->JWT_SCRET());
    }

    /***
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authLogin(User $user)
    {
//        $this->validate($this->request, [
//           'username' => 'required',
//           'password' => 'password',
//        ]);

        $user = User::where('username', $this->request->input('username'))->first();
        if (!$user)
        {
            return $this->BuildResponse(false, "Username not found", $user, 404);
        }

        if ($this->request->input('password') == $user->password)
        {
            return $this->BuildResponse(true, "Login success", $user, 200);
        }

        return $this->BuildResponse(false, "Password is wrong", $user, 400);
    }


}
