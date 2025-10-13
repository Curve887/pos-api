<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{

    public function me(Request $request) {
            
        $user = DB::table('users')
        ->join('roles', 'roles.id', '=', 'users.role_id')
        ->select('users.id', 'users.name', 'users.email', 'roles.name as role')
        ->where('users.id', $request->user()->id)
        ->first();

        return response()->json($user);
    }
   
    public function getAllUser() {

        $user = DB::table('users')
        ->join('roles', 'roles.id', '=', 'users.role_id')
        ->select('users.id', 'users.name', 'users.email', 'roles.name as role')
        ->get();

        return response()->json($user);
    }

    public function getUserById($id)
    {
        $user = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->select('users.id', 'users.name', 'users.email', 'roles.name as role')
            ->where('users.id', $id)
            ->first();

            if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    return response()->json($user);
    }

}
