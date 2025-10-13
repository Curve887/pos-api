<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function register(Request $request)
        {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:customers,email',
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/[A-Z]/',         // harus ada huruf besar
                'regex:/[a-z]/',         // harus ada huruf kecil
                'regex:/[0-9]/',         // harus ada angka
            ],
            'phone'    => 'required|string|unique:customers,phone|max:20|regex:/^[0-9]+$/', // hanya angka
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
            'message' => 'Customer berhasil register',
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
    }

     public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        if (!$customer || !Hash::check($validated['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Buat token
        $token = $customer->createToken('api_customer_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'customer' => $customer,
                'token' => $token
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}
