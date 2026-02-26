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
        Schema::table('expenses', function (Blueprint $table) {
            // Make columns nullable
            $table->string('description')->nullable()->change();
            $table->string('new_category')->nullable()->change();

            // Fix deleted_at to use proper soft deletes timestamp
            $table->dateTime('deleted_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('description')->change();
            $table->string('new_category')->change();
            $table->date('deleted_at')->change();
        });
    }
};
