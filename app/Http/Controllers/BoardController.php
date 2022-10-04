<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Board_member;
use App\Models\Login_token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();

        if($user){
            $boards = Board::all();
            return response()->json([
                'data' => $boards,
                'msg' => 'Get boards success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only logged in user can access this endpoint'
            ], 401);
        }
    }

    public function open(Request $request, $id)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();
        $exist = Board_member::where('board_id', $id)->where('user_id', $user->id)->exists();
        if($exist){
            $board = Board::with(['members', 'lists'])->get();
            return response()->json([
                'data' => $board,
                'msg' => 'Open board success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only team member can access this endpoint'
            ], 401);
        }
    }

    public function add_member(Request $request, $id)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();
        $exist = Board_member::where('board_id', $id)->where('user_id', $user->id)->exists();

        if($exist){
            $validator = Validator::make($request->all(), [
                'username' => 'required|exists:users'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'data' => [],
                    'msg' => $validator->errors()
                ], 422);
            }

            $new_member = User::where('username', $request->username)->first();

            Board_member::create([
                'board_id' => $id,
                'user_id' => $new_member->id
            ]);

            return response()->json([
                'data' => [],
                'msg' => ' Add member success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only team member can access this endpoint'
            ], 401);
        }
    }

    public function remove_member(Request $request, $id)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();
        $exist = Board_member::where('board_id', $id)->where('user_id', $user->id)->exists();

        if($exist){
            $validator = Validator::make($request->all(), [
                'username' => 'required|exists:users'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'data' => [],
                    'msg' => $validator->errors()
                ], 422);
            }

            $new_member = User::where('username', $request->username)->first();

            Board_member::create([
                'board_id' => $id,
                'user_id' => $new_member->id
            ]);

            return response()->json([
                'data' => [],
                'msg' => ' Add member success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only team member can access this endpoint'
            ], 401);
        }
    }

    public function store(Request $request)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();

        if($token){
            $validator = Validator::make($request->all(), [
                'name' => 'required'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'data' => [],
                    'msg' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['creator_id'] = $user->id;

            $board = Board::create($data);

            Board_member::create([
                'board_id' => $board->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'data' => $data,
                'msg' => 'Board created'
            ], 401);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only logged in user can access this endpoint'
            ], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'data' => [],
                'msg' => $validator->errors()
            ], 422);
        }

        $same = Board::where('id', $id)->where('creator_id', $user->id)->exists();

        if($same){
            $board = Board::where('id', $id)->update($request->all());

            return response()->json([
                'data' => $board,
                'msg' => 'Update success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only team member can access this endpoint'
            ], 401);
        }
    }

    public function destroy(Request $request, $id)
    {
        $token = Login_token::where('token', $request->bearerToken())->first();
        $user = User::where('id', $token->id)->first();
        $exist = Board::where('id', $id)->where('creator_id', $user->id)->exists();

        if($exist){
            return response()->json([
                'data' => [],
                'msg' => 'Delete success'
            ], 200);
        }else{
            return response()->json([
                'data' => [],
                'msg' => 'Only creator can access this endpoint'
            ], 200);
        }
    }
}
