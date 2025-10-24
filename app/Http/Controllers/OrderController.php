<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Store a newly created order from the current user's cart.
     * This is the USER endpoint for placing an order.
     */
    public function createOrder(Request $request)
    {
        // 1. Basic validation
        $request->validate([
            'user_id' => 'required|exists:users,id', // Assuming user is authenticated and passed
            'phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            
        ]);

        $userId = $request->user_id;

        // 2. Fetch cart items
        $cartItems = Cart::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        // 3. Calculate total
        $totalAmount = $cartItems->sum(function ($item) {
            return $item->product_price * $item->quantity;
        });

        DB::beginTransaction();

        try {
            // 4. Create the Order
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'shipping_address' => $request->shipping_address,
                'phone' => $request->phone,
                'payment_method' => $request->payment_method,
                'status' => 'pending', // Default status for new orders
            ]);

            // 5. Move items from Cart to OrderItems
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product_name,
                    'product_image' => $cartItem->product_image,
                    'product_price' => $cartItem->product_price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->product_price * $cartItem->quantity,
                ]);

                // TODO: Deduct inventory quantity here if applicable
            }

            // 6. Clear the cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully!',
                'data' => $order->load('orderItems')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to place order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all orders for a specific user.
     * This is the USER endpoint for viewing order history.
     */
    public function getUserOrders($userId)
    {
        $orders = Order::where('user_id', $userId)
                        ->with('orderItems')
                        ->orderByDesc('created_at')
                        ->get();

        return response()->json(['data' => $orders]);
    }

    /**
     * Get details for a specific order.
     * Used by both user and admin for modal viewing.
     */
    public function getOrderDetails($orderId)
    {
        $order = Order::where('id', $orderId)
                        ->with(['user:id,name,email', 'orderItems']) // Load user (specific fields) and items
                        ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['data' => $order]);
    }

    // --------------------------------------------------------------------------
    // ADMIN MANAGEMENT ENDPOINTS
    // --------------------------------------------------------------------------

    /**
     * Admin: Fetch ALL orders.
     * Route: GET /api/orders/all
     */
    public function getAllOrders()
    {
        $orders = Order::with(['user:id,name', 'orderItems'])
                        ->orderByDesc('created_at')
                        ->get();

        return response()->json(['data' => $orders]);
    }

    /**
     * Admin: Fetch orders filtered by status.
     * Route: GET /api/orders/status/{status}
     */
    public function getOrdersByStatus($status)
    {
        $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];

        if (!in_array(strtolower($status), $allowedStatuses)) {
            return response()->json(['message' => 'Invalid status filter provided.'], 400);
        }

        $orders = Order::where('status', strtolower($status))
                        ->with(['user:id,name', 'orderItems'])
                        ->orderByDesc('created_at')
                        ->get();

        return response()->json(['data' => $orders]);
    }

    /**
     * Admin: Update the status of a specific order.
     * Route: PUT /api/orders/{orderId}/status
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
        ]);

        $newStatus = strtolower($request->status);
        $order->status = $newStatus;
        $order->save();

        // TODO: Add logic here for sending confirmation emails/notifications based on status change

        return response()->json([
            'message' => "Order #{$order->order_number} status updated to '{$newStatus}' successfully.",
            'data' => $order
        ]);
    }

    /**
     * Admin: Cancel an order (simple status update to 'cancelled').
     * Route: DELETE /api/orders/{orderId}/cancel (This route is redundant but kept for completeness if needed)
     */
    public function cancelOrder($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return response()->json(['message' => 'Order cannot be cancelled in its current state.'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        // TODO: Handle inventory restoration if cancelled

        return response()->json([
            'message' => "Order #{$order->order_number} has been cancelled.",
            'data' => $order
        ]);
    }
}