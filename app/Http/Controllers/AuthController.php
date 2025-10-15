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
public function update(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) return response()->json(['message' => 'User not found'], 404);

    if ($request->has('name')) $user->name = $request->name;
    if ($request->has('email')) $user->email = $request->email;
    if ($request->has('password')) $user->password = Hash::make($request->password);

    $user->save();

    return response()->json([
        'message' => 'User updated successfully',
        'user' => $user
    ]);
}



}
