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
        Schema::table('people', function (Blueprint $table) {
            $table->foreignId('birthplace_id')->nullable()->after('pob')->constrained('places')->onUpdate('cascade')->onDelete('set null');
            $table->index('birthplace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['birthplace_id']);
            $table->dropIndex(['birthplace_id']);
            $table->dropColumn('birthplace_id');
        });
    }
};
