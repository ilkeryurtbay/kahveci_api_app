<?php

// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

// Kullanıcı kaydı ve giriş rotaları
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Ürünler rotası (giriş yapmış kullanıcılara özel)
Route::middleware('auth:sanctum')->get('/products', [ProductController::class, 'index']);

// Sepet işlemleri rotaları (giriş yapmış kullanıcılara özel)
Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::post('/create', [CartController::class, 'createCart']);
    Route::post('/', [CartController::class, 'getCart']);
    Route::post('/add', [CartController::class, 'addItemToCart']);
    Route::delete('/remove', [CartController::class, 'removeItemFromCart']);
    Route::put('/update', [CartController::class, 'updateCartItemQuantity']);
    Route::post('/confirm', [CartController::class, 'cartConfirmOrder']);

});

// Kullanıcı bilgilerini getirme rotası
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

