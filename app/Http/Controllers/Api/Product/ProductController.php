<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
 
    private function baseProductQuery() {
        
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
        $products = $this->baseProductQuery()->get();
        return response()->json($products);
    }

 

    public function store(Request $request){
       $data = $request->only(['name','price', 'stock', 'category_id']);
        
        DB::table('products')->insert($data);

        return response()->json(['message' =>'Produk Berhasil Ditambahkan']);
    }

    

    public function show($id)
     {
        $product = $this->baseProductQuery()->where('products.id', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Produk Tidak Ditemukan'], 404);
        }

        return response()->json($product);
    }

    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        $data = $request->only(['name','price', 'stock', 'category_id']);

        if (!DB::table('products')->where('id', $id)->exists()) {
            return response()->json(['message' => 'Produk Tidak Ditemukan'], 404);
        }

        DB::table('products')->where('id', $id)->update($data);
        
        return response()->json(['message' => 'Produk Berhasil Diupdate']);
    }


    public function destroy(string $id)
    {
        DB::table('products')->where('id', $id)->delete();

        if (!DB::table('products')->where('id', $id)->exists()) {
            return response()->json(['message' => 'Produk Tidak Ditemukan'], 404);
        }

        return response()->json(['message' => 'Produk Berhasil Dihapus']);
    }
}
