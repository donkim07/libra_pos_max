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
Schema::create('stock_adjustments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('item_id')->constrained();
    $table->foreignId('store_id')->constrained();

    // Nullable: manual adjustments won’t have manufacturing_id
    $table->foreignId('manufacturing_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->enum('type', ['increase', 'decrease']);

    // Signed quantity (+5 or -2)
    $table->decimal('quantity_change', 10, 2);

    $table->decimal('quantity_before', 10, 2);
    $table->decimal('quantity_after', 10, 2);

    $table->text('reason')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users');

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
