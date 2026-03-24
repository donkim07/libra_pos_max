<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('stock_movements', function (Blueprint $table) {
    $table->id();

    $table->foreignId('item_id')->constrained('items');

    $table->decimal('quantity', 12, 4);
    $table->decimal('unit_cost', 12, 4)->nullable();
    $table->decimal('total_cost', 12, 4)->nullable();

    $table->enum('type', [
        'purchase',
        'sale',
        'manufacture_in',
        'manufacture_out',
        'adjustment'
    ]);

    $table->nullableMorphs('reference');

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
