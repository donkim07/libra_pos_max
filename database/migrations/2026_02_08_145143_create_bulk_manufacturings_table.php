<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_manufacturings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->comment('Bulk assembly item');
            $table->decimal('quantity', 15, 4);
            $table->date('date_manufactured');
            $table->foreignId('store_id')->constrained('stores');
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_manufacturings');
    }
};
