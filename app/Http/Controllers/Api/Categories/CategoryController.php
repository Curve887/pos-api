<?php

namespace App\Http\Controllers\Api\Categories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class CategoryController extends Controller
{
    /**
     * Tampilkan semua kategori
     */
    public function index()
    {
        try {
            $categories = DB::table('categories')->orderBy('id', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar kategori berhasil diambil.',
                'data' => $categories,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tambah kategori baru
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            if (DB::table('categories')->where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori sudah ada.',
                ], 409);
            }

            DB::table('categories')->insert([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan.',
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
                'message' => 'Terjadi kesalahan saat menambahkan kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan kategori berdasarkan ID
     */
    public function show($id)
    {
        try {
            $category = DB::table('categories')->where('id', $id)->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail kategori berhasil diambil.',
                'data' => $category,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update kategori
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            $updated = DB::table('categories')->where('id', $id)->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'updated_at' => now(),
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori gagal diupdate atau tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diupdate.',
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
                'message' => 'Terjadi kesalahan saat memperbarui kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus kategori
     */
    public function destroy($id)
    {
        try {
            $deleted = DB::table('categories')->where('id', $id)->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
