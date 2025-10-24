<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Total customers (users with role = 'user')
    public function totalCustomers()
    {
        $count = User::where('role', 'user')->count();
        return response()->json(['count' => $count]);
    }

    // Total sales (sum of all completed orders)
    public function totalSales()
    {
       $total = Order::where('status', 'completed')->sum('total_amount');
 // If you want only completed: ->where('status','completed')->sum('total_amount');
        return response()->json(['total' => $total]);
    }
}
