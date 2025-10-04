<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->onUpdate('cascade')->onDelete('cascade');
            $table->index('team_id');
        });

        // Update existing places - assign them to teams based on people who use them
        // For each place, find the first person who uses it and assign the place to that person's team
        DB::statement('
            UPDATE places 
            SET team_id = (
                SELECT team_id 
                FROM people 
                WHERE people.birthplace_id = places.id 
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 
                FROM people 
                WHERE people.birthplace_id = places.id
            )
        ');

        // Delete places that have no people assigned (orphaned places)
        DB::statement('
            DELETE FROM places 
            WHERE team_id IS NULL
        ');

        // Now make team_id NOT NULL since all valid places should have a team
        Schema::table('places', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable(false)->change();
        });

        // Update unique constraint to include team_id
        Schema::table('places', function (Blueprint $table) {
            $table->dropUnique('places_name_postal_unique');
            $table->unique(['team_id', 'name', 'postal_code'], 'places_team_name_postal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropUnique('places_team_name_postal_unique');
            $table->unique(['name', 'postal_code'], 'places_name_postal_unique');
        });

        Schema::table('places', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropIndex(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
