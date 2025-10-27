<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Fix double-encoded amenities in room_types table
        $roomTypes = DB::connection('tenant')->table('room_types')->get();
        foreach ($roomTypes as $roomType) {
            $raw = $roomType->amenities;
            $decoded = json_decode($raw, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                DB::table('room_types')
                    ->where('id', $roomType->id)
                    ->update(['amenities' => json_encode($decoded)]);
            }
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
