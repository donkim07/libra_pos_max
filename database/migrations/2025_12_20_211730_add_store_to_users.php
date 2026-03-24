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
        Schema::table('users', function (Blueprint $table) {
            //update users table to add store_id foreign key
            $table->after('remember_token', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            });

        });
    }

    /**
     * Reverse the migrations.
     */

};
