<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select(
            'products.id',
            'products.name',
            'products.price',
            'products.stock',
            'categories.name as category'
        )
        ->get();
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $data = $request->only(['name','price', 'stock', 'category_id']);
        
        DB::table('products')->insert($data);

        return response()->json(['message' =>'Produk Berhasil Ditambahkan']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select(
            'products.id',
            'products.name',
            'products.price',
            'products.stock',
            'categories.name as category_name'
        )
        ->where('id', $id)->first();

        if (!$products) {
            return response()->json(['message' => 'Produk Tidak Ditemukan'], 404);
        }

        return response()->json($products);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->only(['name','price', 'stock', 'category_id']);

        DB::table('products')->where('id', $id)->update($data);
        
        return response()->json(['message' => 'Produk Berhasil Diupdate']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table('products')->where('id', $id)->delete();

        return response()->json(['message' => 'Produk Berhasil Dihapus']);
    }
}
