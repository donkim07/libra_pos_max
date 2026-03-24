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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->nullable();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade')->nullable();
            $table->decimal('total', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.0);
            // $table->decimal('discount', 15, 2)->default(0.0);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade')->nullable();
            $table->foreignId('account_id')->constrained()->onDelete('cascade')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
