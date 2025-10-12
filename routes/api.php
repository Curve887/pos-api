<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Product\ProductController;

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

    // ===============================
    // Protected routes (dengan Sanctum)
    // ===============================

    Route::middleware('auth:sanctum')->group(function() {
        // Group untuk AUTH
        Route::prefix('profile')->group(function () {
            Route::get('/me', [AuthController::class, 'me']); // GET user yang sdg login
            Route::get('/', [AuthController::class, 'getAllUser']); //  GET all users
            Route::get('/{id}', [AuthController::class, 'getUserById']); // GET user by ID
            Route::post('/logout', [AuthController::class, 'logout']); 
        });
        // Group untuk PRODUCT
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);              // GET semua produk
            Route::post('/', [ProductController::class, 'store']);             // POST tambah produk
            Route::get('/{id}', [ProductController::class, 'show']);           // GET detail produk
            Route::put('/{id}', [ProductController::class, 'update']);         // PUT update produk
            Route::delete('/{id}', [ProductController::class, 'destroy']);     // DELETE hapus produk
        });
    });