<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ]);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ]);
    }

    // GET USER BY EMAIL
    public function getUser(Request $request)
    {
        $email = $request->query('email');
        if (!$email) return response()->json(['message' => 'Email is required'], 400);

        $user = User::where('email', $email)->first();
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        return response()->json($user);
    }

    // UPDATE USER
    public function update(Request $request)
    {
        $currentEmail = $request->input('email');
        $user = User::where('email', $currentEmail)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found or not authenticated'], 404);
        }

        $rules = [];
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('username')) {
            $rules['username'] = 'string|max:255|unique:users,username,' . $user->id;
            $user->username = $request->username;
        }
        if ($request->has('new_email')) {
            $rules['new_email'] = 'string|email|max:255|unique:users,email';
            $user->email = $request->new_email;
        }
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('password')) {
            $rules['password'] = 'string|min:8';
            $user->password = Hash::make($request->password);
        }

        if (!empty($rules)) {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }
    // GET ALL CUSTOMERS (Admin only)
public function getAllCustomers()
{
     try {
            // Only fetch users with role 'user', exclude admins
            $customers = User::where('role', 'user')
                             ->orderBy('created_at', 'desc')
                             ->get();
            
            return response()->json([
                'data' => $customers,
                'message' => 'Customers fetched successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch customers',
                'error' => $e->getMessage()
            ], 500);
        }
}

// DELETE CUSTOMER (Admin only)
public function deleteCustomer($id)
{
    try {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        
        // Optional: Prevent deleting admin users
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin users'], 403);
        }
        
        $user->delete();
        
        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete customer',
            'error' => $e->getMessage()
        ], 500);
    }
}
}