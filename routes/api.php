<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactMessageController;

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
