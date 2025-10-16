<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ]);
    }
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    return response()->json([
        'message' => 'Login successful',
        'user' => $user,
    ]);
}
public function getUser(Request $request)
{
    // Frontend sends email as query param
    $email = $request->query('email');

    if (!$email) {
        return response()->json(['message' => 'Email is required'], 400);
    }

    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json($user);
}
// INSIDE AuthController.php

public function update(Request $request)
{
    // 1. Identify the user using the 'email' sent from the frontend
    // This assumes the frontend sends the user's current email for identification
    $currentEmail = $request->input('email');
    $user = User::where('email', $currentEmail)->first();

    if (!$user) {
        // Return a 404 error if the user isn't found
        return response()->json(['message' => 'User not found or not authenticated'], 404);
    }
    
    $rules = [];

    // 2. Handle Name update
    if ($request->has('name')) {
        $user->name = $request->name;
    }
    
    // 3. Handle Email update (The frontend sends this as 'new_email')
    if ($request->has('new_email')) {
        $rules['new_email'] = 'required|string|email|max:255|unique:users,email';
        $user->email = $request->new_email;
    }

    // 4. Handle Password update
    if ($request->has('password')) {
        $rules['password'] = 'required|string|min:8';
        $user->password = Hash::make($request->password);
    }

    // 5. Run validation before saving
    if (!empty($rules)) {
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Failed', 'errors' => $validator->errors()], 422);
        }
    }

    $user->save();

    // 6. Return the updated user data
    return response()->json([
        'message' => 'User updated successfully',
        'user' => $user // Return the updated user object
    ]);
}


}
