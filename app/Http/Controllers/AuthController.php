<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Login_token;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function createToken($id)
    {
        return bcrypt($id);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:5|max:12',
            'password' => 'required|min:5|max:12',
        ]);

        if($validator->fails()){
            return response()->json([
                'data' => [],
                'msg' => $validator->errors(),
            ], 422);
        }

        if(Auth::attempt($request->only('username', 'password'))){
            $user = Auth::user();
            $token = $this->createToken($user->id);
            Login_token::where('user_id', $user->id)->update(['token' => $token]);
            return response()->json([
                'data' => [
                    'user' => $user,
                    'token' => $token
                ],
                'msg' => 'Login success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Username / password invalid'
            ], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:2|max:20',
            'last_name' => 'required|min:2|max:20',
            'username' => 'required|min:5|max:12|unique:users|alpha_num|alpha_dash',
            'password' => 'required|min:5|max:12',
        ]);

        if($validator->fails()){
            return response()->json([
                'data' => [],
                'msg' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        $token = $this->createToken($user->id);
        Login_token::create($token);

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token
            ],
            'msg' => 'Register success'
        ], 200);
    }

    public function logout(Request $request)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        if($token){
            $token->delete();
            return response()->json([
                'data' => [],
                'msg' => 'Logout success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Invalid token'
            ], 401);
        }
    }
}
