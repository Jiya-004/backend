<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/user', [AuthController::class, 'getUser']);
Route::put('/user', [AuthController::class, 'update']); 
Route::post('/contact', [ContactMessageController::class, 'store']);
Route::get('/contact', [ContactMessageController::class, 'index']); 
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

// Cart routes
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'index']);
Route::get('/cart/count', [CartController::class, 'getCount']); // Add this
Route::put('/cart/{id}', [CartController::class, 'updateQuantity']);
Route::delete('/cart/{id}', [CartController::class, 'remove']);
Route::delete('/cart', [CartController::class, 'clear']);

// Order routes
Route::post('/orders', [OrderController::class, 'createOrder']);
Route::get('/orders/user/{userId}', [OrderController::class, 'getUserOrders']);
Route::get('/orders/{orderId}', [OrderController::class, 'getOrderDetails']);
Route::put('/orders/{orderId}/status', [OrderController::class, 'updateOrderStatus']);
Route::delete('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']);