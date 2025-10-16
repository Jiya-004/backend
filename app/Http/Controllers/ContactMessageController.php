<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    // Store message
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        ContactMessage::create($validated);

        return response()->json(['success' => true, 'message' => 'Message sent successfully!'], 201);
    }

    // Optional: show all messages
    public function index()
    {
        return response()->json(ContactMessage::all());
    }
}
