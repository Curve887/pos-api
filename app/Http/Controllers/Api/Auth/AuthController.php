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
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role_id' => 'nullable|integer|exists:roles,id',
            ]);

            // Jika role_id tidak dikirim, default ke role kasir (id = 2)
            $roleId = $validated['role_id'] ?? 2;

            $userId = DB::table('users')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'roles.name as role')
                ->where('users.id', $userId)
                ->first();

            $token = User::find($userId)->createToken('api_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi Berhasil',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi',
                'error' => $e->getMessage(),
            ], 500);
        }
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
