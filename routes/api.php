<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

// --- Authentication & User Management ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/user', [AuthController::class, 'getUser']);
Route::put('/user', [AuthController::class, 'update']); 

// --- Contact Form ---
Route::post('/contact', [ContactMessageController::class, 'store']);

// --- Product Listing (Public) ---
Route::get('/products', [ProductController::class, 'index']);

// --- Cart Routes (Typically require authentication) ---
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'index']);
Route::get('/cart/count', [CartController::class, 'getCount']);
Route::put('/cart/{id}', [CartController::class, 'updateQuantity']);
Route::delete('/cart/{id}', [CartController::class, 'remove']);
Route::delete('/cart', [CartController::class, 'clear']);

// --- User-Facing Order Routes ---
Route::post('/orders', [OrderController::class, 'createOrder']);
Route::get('/orders/user/{userId}', [OrderController::class, 'getUserOrders']);
Route::get('/orders/{orderId}', [OrderController::class, 'getOrderDetails']); // Used for both user/admin detail viewing


// --- ADMIN MANAGEMENT ENDPOINTS (Typically require 'admin' middleware) ---

// Product Management
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

// Contact Messages (Admin viewing all)
Route::get('/contact/all', [ContactMessageController::class, 'index']);

// Order Management
Route::get('/orders/all', [OrderController::class, 'getAllOrders']); // Admin Order History (all)
Route::get('/orders/status/{status}', [OrderController::class, 'getOrdersByStatus']); // Admin New Orders (filtered)
Route::put('/orders/{orderId}/status', [OrderController::class, 'updateOrderStatus']); // Change status (e.g., pending -> processing)
Route::delete('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']); // Cancel specific order

// Admin Dashboard Metrics
Route::prefix('admin')->group(function () {
    Route::get('/total-customers', [AdminController::class, 'totalCustomers']);
    Route::get('/total-sales', [AdminController::class, 'totalSales']);
    Route::get('/messages', [ContactMessageController::class, 'index']); 

     // Debug endpoints
     
});
// --- ADMIN MANAGEMENT ENDPOINTS ---

// ... your existing admin routes ...

// Customer Management (Add these lines)
Route::get('/admin/customers', [AuthController::class, 'getAllCustomers']);
Route::delete('/admin/customers/{id}', [AuthController::class, 'deleteCustomer']);