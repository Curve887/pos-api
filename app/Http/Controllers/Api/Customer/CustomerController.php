<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Customer;
use Exception;

class CustomerController extends Controller
{
    /**
     * Register Customer Baru
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:100',
                'email'    => 'required|email|unique:customers,email',
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'regex:/[A-Z]/', // huruf besar
                    'regex:/[a-z]/', // huruf kecil
                    'regex:/[0-9]/', // angka
                ],
                'phone'    => 'required|string|unique:customers,phone|max:20|regex:/^[0-9]+$/',
                'address'  => 'nullable|string|max:255',
            ], [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah digunakan.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
                'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
                'phone.required' => 'Nomor telepon wajib diisi.',
                'phone.unique' => 'Nomor telepon sudah digunakan.',
                'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            ]);

            $customerId = DB::table('customers')->insertGetId([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'phone'      => $validated['phone'],
                'address'    => $validated['address'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $customer = Customer::find($customerId);

            $token = $customer->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil register.',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'address' => $customer->address,
                    ],
                    'token' => $token,
                ],
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
                'message' => 'Terjadi kesalahan saat registrasi customer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login Customer
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]);

            $customer = Customer::where('email', $validated['email'])->first();

            if (!$customer || !Hash::check($validated['password'], $customer->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah.',
                ], 401);
            }

            $token = $customer->createToken('api_customer_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'customer' => $customer,
                    'token' => $token,
                ],
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

    /**
     * Logout Customer
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
