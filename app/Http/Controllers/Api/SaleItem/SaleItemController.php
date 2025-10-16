<?php

namespace App\Http\Controllers\Api\SaleItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class SaleItemController extends Controller
{
    /**
     * Tampilkan semua item penjualan.
     */
    public function index()
    {
        try {
            $items = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->select(
                    'sale_items.id',
                    'sale_items.sale_id',
                    'sales.invoice_number',
                    'products.name as product_name',
                    'sale_items.quantity',
                    'sale_items.price',
                    'sale_items.subtotal',
                    'sale_items.created_at'
                )
                ->orderByDesc('sale_items.created_at')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar semua item penjualan',
                'data' => $items,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tambah item penjualan baru.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sale_id'    => 'required|exists:sales,id',
                'product_id' => 'required|exists:products,id',
                'quantity'   => 'required|integer|min:1',
                'price'      => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subtotal = $request->quantity * $request->price;

            DB::beginTransaction();

            $itemId = DB::table('sale_items')->insertGetId([
                'sale_id'    => $request->sale_id,
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity,
                'price'      => $request->price,
                'subtotal'   => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')
                ->where('id', $request->product_id)
                ->decrement('stock', $request->quantity);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item penjualan berhasil ditambahkan',
                'data' => [
                    'id' => $itemId,
                    'subtotal' => $subtotal,
                ],
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item penjualan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan detail 1 item penjualan.
     */
    public function show($id)
    {
        try {
            $item = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                    'sale_items.id',
                    'sales.invoice_number',
                    'products.name as product_name',
                    'sale_items.quantity',
                    'sale_items.price',
                    'sale_items.subtotal'
                )
                ->where('sale_items.id', $id)
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item penjualan tidak ditemukan',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Detail item penjualan ditemukan',
                'data' => $item,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update data item penjualan.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
                'price'    => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $item = DB::table('sale_items')->where('id', $id)->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item penjualan tidak ditemukan',
                ], 404);
            }

            $subtotal = $request->quantity * $request->price;

            DB::table('sale_items')
                ->where('id', $id)
                ->update([
                    'quantity'   => $request->quantity,
                    'price'      => $request->price,
                    'subtotal'   => $subtotal,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Item penjualan berhasil diperbarui',
                'data' => [
                    'id' => $id,
                    'subtotal' => $subtotal,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui item penjualan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus item penjualan.
     */
    public function destroy($id)
    {
        try {
            $item = DB::table('sale_items')->where('id', $id)->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item penjualan tidak ditemukan',
                ], 404);
            }

            DB::beginTransaction();

            DB::table('products')
                ->where('id', $item->product_id)
                ->increment('stock', $item->quantity);

            DB::table('sale_items')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item penjualan berhasil dihapus dan stok dikembalikan',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item penjualan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
