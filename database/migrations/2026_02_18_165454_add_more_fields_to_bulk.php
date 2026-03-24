<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_manufacturings', function (Blueprint $table) {
            $table->decimal('remaining_quantity', 15, 4)->nullable()->after('quantity');
            $table->boolean('is_finished')->default(false)->after('remaining_quantity');
            $table->decimal('waste_quantity', 15, 4)->default(0)->after('is_finished');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_manufacturings', function (Blueprint $table) {
            $table->dropColumn(['remaining_quantity', 'is_finished', 'waste_quantity']);
        });
    }
};