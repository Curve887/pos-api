<?php

namespace App\Http\Controllers\Api\Categories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Tampilkan semua kategori
     */
    public function index()
    {
        $categories = DB::table('categories')->get();
        return response()->json($categories);
    }

    /**
     * Tambah kategori baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        if (DB::table('categories')->where('name', $validated['name'])->exists()) {
            return response()->json(['message' => 'Kategori sudah ada'], 409);
        }

        DB::table('categories')->insert([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Kategori berhasil ditambahkan']);
    }


    public function show($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json($category);
    }

    /**
     * Update kategori
     */
    public function update(Request $request, $id)
    {
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
            return response()->json(['message' => 'Kategori gagal diupdate atau tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Kategori berhasil diupdate']);
    }

    /**
     * Hapus kategori
     */
    public function destroy($id)
    {
        $deleted = DB::table('categories')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
