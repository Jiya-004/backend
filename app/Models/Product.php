<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // ⬅️ ADD THIS LINE!

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'image',
    ];
}