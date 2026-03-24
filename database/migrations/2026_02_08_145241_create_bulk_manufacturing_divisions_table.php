<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_manufacturing_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_manufacturing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_item_id')->constrained('items');
            $table->decimal('base_quantity_used', 15, 4);
            $table->decimal('quantity_produced', 15, 4);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_manufacturing_divisions');
    }
};
