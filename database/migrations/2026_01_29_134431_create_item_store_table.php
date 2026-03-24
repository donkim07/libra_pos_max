<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->decimal('quantity', 15, 4)->default(0)->comment('Stock quantity in this store');
            $table->timestamps();

            $table->unique(['item_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_store');
    }
};
