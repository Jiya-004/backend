<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Create order from cart
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'shipping_address' => 'required|string',
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userId = $request->user_id;

            // Get cart items
            $cartItems = Cart::where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Calculate total
            $totalAmount = $cartItems->sum(function($item) {
                return $item->product_price * $item->quantity;
            });

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => Order::generateOrderNumber(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'phone' => $request->phone
            ]);

            // Create order items from cart
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product_name,
                    'product_image' => $cartItem->product_image,
                    'product_price' => $cartItem->product_price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->product_price * $cartItem->quantity
                ]);
            }

            // Clear cart after order is created
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            Log::info('Order created successfully:', ['order_id' => $order->id]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order' => $order->load('orderItems')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all orders for a user
    public function getUserOrders($userId)
    {
        try {
            $orders = Order::with('orderItems')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch orders:', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders'
            ], 500);
        }
    }

    // Get single order details
    public function getOrderDetails($orderId)
    {
        try {
            $order = Order::with('orderItems')->findOrFail($orderId);

            return response()->json([
                'success' => true,
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    // Update order status
    public function updateOrderStatus(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::findOrFail($orderId);
            $order->status = $request->status;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    // Cancel order
    public function cancelOrder($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if ($order->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel completed order'
                ], 400);
            }

            $order->status = 'cancelled';
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }
}