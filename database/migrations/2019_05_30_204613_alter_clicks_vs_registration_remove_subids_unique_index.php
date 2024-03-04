<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clicks_vs_registration_statistics', function (Blueprint $table) {

            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('clicks_vs_registration_statistics');
            if (array_key_exists('subids_index', $indexesFound)) {
                $table->dropUnique('subids_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('clicks_vs_registration_statistics', function (Blueprint $table) {
        //     $table->unique(['affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5', 'created_at'], 'subids_index');
        // });
    }
};
