<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // Get all cart items for a user
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'User ID required'], 400);
            }

            Log::info('Fetching cart for user:', ['user_id' => $userId]);

            // Simply fetch cart items (no need for product relationship anymore)
            $cartItems = Cart::where('user_id', $userId)->get();

            // Calculate total
            $total = $cartItems->sum(function($item) {
                return $item->product_price * $item->quantity;
            });

            Log::info('Cart items found:', ['count' => $cartItems->count()]);

            return response()->json([
                'success' => true,
                'data' => $cartItems,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch cart:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Add product to cart
    public function addToCart(Request $request)
    {
        try {
            Log::info('Cart add request received:', $request->all());

            // Validate request with new fields
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'product_id' => 'required|integer',
                'product_name' => 'required|string|max:255',
                'product_image' => 'required|string',
                'product_price' => 'required|numeric|min:0',
                'quantity' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('Validation passed');

            // Check if item already exists in cart
            $cartItem = Cart::where('user_id', $request->user_id)
                           ->where('product_id', $request->product_id)
                           ->first();

            if ($cartItem) {
                // Update quantity if already exists
                $cartItem->quantity += ($request->quantity ?? 1);
                $cartItem->save();
                
                Log::info('Cart item quantity updated:', $cartItem->toArray());
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'data' => $cartItem
                ], 200);
            } else {
                // Create new cart item
                $cartItem = Cart::create([
                    'user_id' => $request->user_id,
                    'product_id' => $request->product_id,
                    'product_name' => $request->product_name,
                    'product_image' => $request->product_image,
                    'product_price' => $request->product_price,
                    'quantity' => $request->quantity ?? 1
                ]);

                Log::info('New cart item created:', $cartItem->toArray());

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'data' => $cartItem
                ], 201);
            }

        } catch (\Exception $e) {
            Log::error('Cart add failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to add to cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Update quantity
    public function updateQuantity(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $cartItem = Cart::findOrFail($id);
            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'data' => $cartItem
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update cart quantity:', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }
    }

    // Remove item
    public function remove($id)
    {
        try {
            $cartItem = Cart::findOrFail($id);
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to remove cart item:', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }
    }

    // Clear cart
    public function clear(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'error' => 'User ID required'
                ], 400);
            }
            
            Cart::where('user_id', $userId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to clear cart:', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get cart item count
    public function getCount(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'error' => 'User ID required'
                ], 400);
            }

            $count = Cart::where('user_id', $userId)->sum('quantity');
            
            return response()->json([
                'success' => true,
                'count' => $count
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get cart count:', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}