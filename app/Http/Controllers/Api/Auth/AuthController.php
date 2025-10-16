<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Exception;

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
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // naik jadi 5MB
            ]);

            $roleId = $validated['role_id'] ?? 2;
            $profileImagePath = null;

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $profileImagePath = $file->store('profile_images', 'public');
            }

            $userId = DB::table('users')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $roleId,
                'profile_image' => $profileImagePath ? asset('storage/' . $profileImagePath) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.profile_image', 'roles.name as role')
                ->where('users.id', $userId)
                ->first();

            $token = User::find($userId)->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registrasi Berhasil',
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = DB::table('users')->where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Email atau Password salah.'],
                ]);
            }

            $token = User::find($user->id)->createToken('api_token')->plainTextToken;

            $userData = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.profile_image', 'roles.name as role')
                ->where('users.id', $user->id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil.',
                'user' => $userData,
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan logout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
