<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends APIController
{
    /**
     * Handle the incoming request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->respond($validator->errors(), 'Validation Error', 400);
        }

        $credentials = $request->only('email', 'password');

        $token = auth()->guard('api')->attempt($credentials);

        if (!$token) {
            return $this->respond(null, 'Unauthorized', 401);
        }
        $data['user'] = auth()->guard('api')->user();
        $data['token'] = $token;

        return $this->respond($data, 'Login successful', 200);
    }

    public function logout(Request $request)
    {
        auth()->guard('api')->logout();

        return $this->respond(null, 'User logged out successfully', 200);
    }
}
