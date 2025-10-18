<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['product_id']);
            
            // Add new columns
            $table->string('product_name')->after('product_id');
            $table->string('product_image')->after('product_name');
            $table->decimal('product_price', 10, 2)->after('product_image');
            
            // Add unique constraint
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Remove the unique constraint
            $table->dropUnique(['user_id', 'product_id']);
            
            // Drop new columns
            $table->dropColumn(['product_name', 'product_image', 'product_price']);
            
            // Add back the foreign key
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};