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
        //
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('source_store')->nullable()->after('quantity');
            $table->string('destination_store')->nullable()->after('source_store');
            $table->string('reference_code')->nullable()->after('destination_store');
            $table->foreignId('created_by')->nullable()->after('reference_code')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
