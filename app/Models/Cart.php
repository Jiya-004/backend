<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'product_image',
        'product_price',
        'quantity'
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}