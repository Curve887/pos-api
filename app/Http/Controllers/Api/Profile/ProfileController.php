<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        try {
            $user = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.profile_image', 'roles.name as role')
                ->where('users.id', $request->user()->id)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data profil berhasil diambil.',
                'data' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllUser()
    {
        try {
            $users = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.profile_image', 'roles.name as role')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar user berhasil diambil.',
                'data' => $users
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserById($id)
    {
        try {
            $user = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.profile_image', 'roles.name as role')
                ->where('users.id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil.',
                'data' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'nullable|string|max:100',
                'email' => 'nullable|email|unique:users,email,' . $request->user()->id,
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $userId = $request->user()->id;
            $user = DB::table('users')->where('id', $userId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan.'
                ], 404);
            }

            $profileImagePath = $user->profile_image;

            // Upload gambar baru
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');

                // Hapus foto lama jika ada
                if ($user->profile_image && Storage::disk('public')->exists(str_replace('storage/', '', $user->profile_image))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $user->profile_image));
                }

                // Simpan file baru ke folder storage/app/public/profile_images
                $path = $file->store('profile_images', 'public');
                $profileImagePath = asset('storage/' . $path);
            }

            // Update data user
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'name' => $request->filled('name') ? $request->name : $user->name,
                    'email' => $request->filled('email') ? $request->email : $user->email,
                    'profile_image' => $profileImagePath,
                    'updated_at' => now(),
                ]);

            $updatedUser = DB::table('users')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.profile_image', 'roles.name as role')
                ->where('users.id', $userId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
                'data' => $updatedUser,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
