<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add rejection_reason column
            $table->text('rejection_reason')->nullable()->after('paid_at');
        });

        // Update the status enum to include new statuses
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'confirmed', 'completed', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        // Revert status enum
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
