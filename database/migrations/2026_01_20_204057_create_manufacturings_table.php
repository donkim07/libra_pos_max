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
Schema::create('manufacturings', function (Blueprint $table) {
    $table->id();

    // What are we making?
    $table->foreignId('item_id') // assembly item
        ->constrained('items')
        ->cascadeOnDelete();

    // How many units produced
    $table->decimal('quantity', 10, 2);

    // Where it goes
    $table->foreignId('store_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->decimal('total_cost', 12, 2)->default(0);

    $table->text('notes')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturings');
    }
};
