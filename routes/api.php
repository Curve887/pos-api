<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Categories\CategoryController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Sales\SalesController;
use App\Http\Controllers\Api\SaleItem\SaleItemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
// header('Access-Control-Allow-Origin: *');

    // Public routes (tanpa auth)

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
    Route::prefix('customer')->group(function () {
        Route::post('/register', [CustomerController::class, 'register']);
        Route::post('/login', [CustomerController::class, 'login']);
    });
    
    // ===============================
    // Protected routes (dengan Sanctum)
    // ===============================

    Route::middleware('auth:sanctum')->group(function() {
        // Group untuk AUTH
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'getAllUser']); //  GET all users
            Route::get('/me', [ProfileController::class, 'me']); // GET user yang sdg login
            Route::get('/{id}', [ProfileController::class, 'getUserById']); // GET user by ID
            Route::post('/update', [ProfileController::class, 'updateProfile']); // GET user by ID
            Route::post('/logout', [AuthController::class, 'logout']); 
        });
        // Group untuk PRODUCT
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);              // GET semua produk
            Route::post('/', [ProductController::class, 'store']);             // POST tambah produk
            Route::get('/{id}', [ProductController::class, 'show']);           // GET detail produk
            Route::post('/{id}', [ProductController::class, 'update']);         // PUT update produk
            Route::delete('/{id}', [ProductController::class, 'destroy']);     // DELETE hapus produk
        });
        // Group untuk CATEGORIES
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::get('/{id}', [CategoryController::class, 'show']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);  
        });
        // Group untuk CUSTOMER
        Route::prefix('customer')->group(function () {
            Route::post('/logout', [CustomerController::class, 'logout']);
        });
        // Group untuk SALES
        Route::prefix('sales')->group(function () {
            Route::get('/', [SalesController::class, 'index']);
            Route::post('/', [SalesController::class, 'store']);
            Route::get('/{id}', [SalesController::class, 'show']);
            Route::post('/{id}', [SalesController::class, 'update']);
            Route::delete('/{id}', [SalesController::class, 'destroy']);
        });
        // Group untuk SALE ITEMS
        Route::middleware('auth:sanctum')->prefix('sale-items')->group(function () {
            Route::get('/', [SaleItemController::class, 'index']);       // GET semua item
            Route::post('/', [SaleItemController::class, 'store']);      // POST tambah item
            Route::get('/{id}', [SaleItemController::class, 'show']);    // GET detail item
            Route::put('/{id}', [SaleItemController::class, 'update']);  // PUT update item
            Route::delete('/{id}', [SaleItemController::class, 'destroy']); // DELETE hapus item
        });
    });