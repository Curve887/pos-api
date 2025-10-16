<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Categories\CategoryController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Sales\SalesController;

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
            Route::put('/{id}', [ProductController::class, '    ']);         // PUT update produk
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
        });
    });