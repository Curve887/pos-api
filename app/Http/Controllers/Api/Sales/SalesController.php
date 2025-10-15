<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    public function __construct()
    {
        // pastikan semua endpoint sales harus pakai auth
        $this->middleware('auth:sanctum');
    }

    /**
     * Tampilkan semua data penjualan
     */
    public function index()
    {
        $sales = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.id',
                'sales.invoice_number',
                'sales.total_amount',
                'sales.payment_method',
                'users.name as kasir',
                'customers.name as customer',
                'sales.created_at'
            )
            ->orderByDesc('sales.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi penjualan',
            'data' => $sales,
        ], 200);
    }

    /**
     * Simpan transaksi baru
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Hanya kasir yang boleh membuat transaksi
        if ($user->role_id != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Hanya kasir yang dapat membuat transaksi.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_id'     => 'nullable|exists:customers,id',
            'total_amount'    => 'required|numeric|min:0',
            'payment_method'  => 'required|string|in:cash,transfer,qris',
            'sale_items'      => 'required|array|min:1',
            'sale_items.*.product_id' => 'required|exists:products,id',
            'sale_items.*.quantity'   => 'required|integer|min:1',
            'sale_items.*.price'      => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $invoiceNumber = 'INV-' . now()->format('YmdHis');

            // user_id ambil dari token, bukan dari body
            $saleId = DB::table('sales')->insertGetId([
                'user_id'        => $user->id,
                'customer_id'    => $request->customer_id,
                'invoice_number' => $invoiceNumber,
                'total_amount'   => $request->total_amount,
                'payment_method' => $request->payment_method,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            foreach ($request->sale_items as $item) {
                $subtotal = $item['quantity'] * $item['price'];

                DB::table('sale_items')->insert([
                    'sale_id'    => $saleId,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'subtotal'   => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('products')
                    ->where('id', $item['product_id'])
                    ->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => [
                    'sale_id' => $saleId,
                    'invoice_number' => $invoiceNumber,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan detail transaksi
     */
    public function show($id, Request $request)
    {
        $user = $request->user();

        // Hanya kasir yang boleh melihat detail transaksi
        if ($user->role_id != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Hanya kasir yang dapat melihat detail transaksi.'
            ], 403);
        }

        $sale = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'sales.id',
                'sales.invoice_number',
                'sales.total_amount',
                'sales.payment_method',
                'sales.created_at',
                'users.name as kasir',
                'customers.name as customer'
            )
            ->where('sales.id', $id)
            ->first();

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        $items = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sale_items.sale_id', $id)
            ->select(
                'products.name as product_name',
                'sale_items.quantity',
                'sale_items.price',
                'sale_items.subtotal'
            )
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi ditemukan',
            'data' => [
                'sale' => $sale,
                'items' => $items,
            ],
        ], 200);
    }
}
