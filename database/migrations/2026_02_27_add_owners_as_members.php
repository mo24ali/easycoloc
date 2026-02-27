<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Add collocation owners to the collocation_user table if not already present.
     * This ensures owners are included in expense splits.
     */
    public function up(): void
    {
        // Find all collocations where the owner is not in the collocation_user table
        $collectionsWithoutOwner = DB::table('collocations as c')
            ->leftJoin('collocation_user as cu', function ($join) {
                $join->on('c.owner_id', '=', 'cu.user_id')
                    ->on('c.id', '=', 'cu.collocation_id');
            })
            ->whereNull('cu.id')
            ->select('c.id', 'c.owner_id', 'c.created_at')
            ->get();

        // Add owner to collocation_user table
        foreach ($collectionsWithoutOwner as $collocation) {
            DB::table('collocation_user')->insert([
                'collocation_id' => $collocation->id,
                'user_id' => $collocation->owner_id,
                'role' => 'owner',
                'joined_at' => $collocation->created_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migration (optional - you might want to keep owners in the table).
     */
    public function down(): void
    {
        // Remove owners that were added by this migration
        // Note: This is conservative and only removes recently added owners
        DB::table('collocation_user')
            ->where('role', 'owner')
            ->where('joined_at', '=', DB::raw('created_at'))
            ->where('created_at', '>=', DB::raw("DATE_SUB(NOW(), INTERVAL 1 DAY)"))
            ->delete();
    }
};
