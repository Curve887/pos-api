<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{

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

    public function me(Request $request) {
            
        $user = DB::table('users')
        ->join('roles', 'roles.id', '=', 'users.role_id')
        ->select('users.id', 'users.name', 'users.email', 'roles.name as role')
        ->where('users.id', $request->user()->id)
        ->first();

        return response()->json($user);
    }


    public function register (Request $request) {

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'nullable|exists:roles,id', // validasi role
        ]);

        // Jika role tidak dikirim, default kasir (id = 2)
        $roleId = $validated['role_id'] ?? 2;

        $userId = DB::table('users')->insertGetId([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $roleId,
        ]);

        $user = DB::table('users')
        ->join('roles','roles.id','=','users.role_id')
        ->select('users.id','users.name','users.email','roles.name as role')
        ->where('users.id', $userId)
        ->first();
        
        // buat token sanctum
        $token = User::find($userId)->createToken('api_token')->plainTextToken;

        return response()->json([
           'message' => 'Registrasi Berhasil',
           'user' => $user,
           'token'  => $token, 
        ]);
    }   

    public function login(Request $request) {

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table('users')->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau Password salah'],
            ]);
        }

        $token = User::find($user->id)->createToken('api_token')->plainTextToken;

        $userData = DB::table('users')
        ->join('roles', 'roles.id', '=', 'users.role_id')
        ->select('users.id', 'users.name', 'users.email', 'roles.name as role')
        ->where('users.id', $user->id)
        ->first();

        return response()->json([
            'message' => 'Login Berhasil',
            'user' => $userData,
            'token' => $token,
        ]);
    }

    public function logout(Request $request) {

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Berhasil'
        ]);
    }

    


}
