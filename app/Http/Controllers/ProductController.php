<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
    return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    // ✅ Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'quantity' => 'nullable|integer|min:0',
    ]);

    // ✅ Handle image upload
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
    }

    // ✅ Create new product
    $product = Product::create([
        'name' => $request->name,
        'price' => $request->price,
        'image' => $imagePath,
        'quantity' => $request->quantity ?? 0,  // ✅ Use 0 as default if null
    ]);

    return response()->json([
        'message' => 'Product added successfully!',
        'product' => $product
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
         $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric',
        'quantity' => 'sometimes|integer|min:0',  // ✅ Add this
    ]);

    $product->update([
        'name' => $request->name,
        'price' => $request->price,
        'quantity' => $request->quantity, // ✅ Add this
    ]);

    return response()->json([
        'message' => 'Product updated successfully',
        'product' => $product
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
          try {
        $product->delete();
        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete product',
            'error' => $e->getMessage()
        ], 500);
    }
}
}