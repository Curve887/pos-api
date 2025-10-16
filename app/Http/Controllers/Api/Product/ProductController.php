<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductController extends Controller
{
    private function baseProductQuery()
    {
        return DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'categories.name as category_name'
            );
    }

    public function index()
    {
        try {
            $products = $this->baseProductQuery()->orderBy('products.id', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar produk berhasil diambil.',
                'data' => $products
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id'
            ]);

            DB::table('products')->insert($validated);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan.'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->baseProductQuery()->where('products.id', $id)->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id'
            ]);

            if (!DB::table('products')->where('id', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.'
                ], 404);
            }

            DB::table('products')->where('id', $id)->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $product = DB::table('products')->where('id', $id)->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.'
                ], 404);
            }

            DB::table('products')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
