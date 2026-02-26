<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('collocation_user', function (Blueprint $table) {
            $table->enum('role', ['member', 'owner'])->default('member')->after('user_id');
            $table->decimal('balance', 10, 2)->default(0)->after('role');
            $table->timestamp('left_at')->nullable()->after('joined_at');
        });
    }

    public function down(): void
    {
        Schema::table('collocation_user', function (Blueprint $table) {
            $table->dropColumn(['role', 'balance', 'left_at']);
        });
    }
};
