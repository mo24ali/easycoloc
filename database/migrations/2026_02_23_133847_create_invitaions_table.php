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
        Schema::create('invitaions', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->enum('status',['accepted','pending']);
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->constrained('users');
            $table->foreignId('collocation_id')->constrained('collocations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitaions');
    }
};
