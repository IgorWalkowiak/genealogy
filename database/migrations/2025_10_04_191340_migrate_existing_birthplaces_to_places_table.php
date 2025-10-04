<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $uniquePlaces = DB::table('people')
            ->select('pob')
            ->whereNotNull('pob')
            ->where('pob', '!=', '')
            ->distinct()
            ->pluck('pob');

        foreach ($uniquePlaces as $placeName) {
            $placeId = DB::table('places')->insertGetId([
                'name' => $placeName,
                'postal_code' => null,
                'latitude' => null,
                'longitude' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('people')
                ->where('pob', $placeName)
                ->update(['birthplace_id' => $placeId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Przywróć dane z birthplace_id do pob
        DB::table('people')
            ->whereNotNull('birthplace_id')
            ->update([
                'pob' => DB::raw('(SELECT name FROM places WHERE places.id = people.birthplace_id)'),
            ]);

        // Wyczyść birthplace_id
        DB::table('people')->update(['birthplace_id' => null]);

        // Wyczyść tabelę places
        DB::table('places')->truncate();
    }
};
