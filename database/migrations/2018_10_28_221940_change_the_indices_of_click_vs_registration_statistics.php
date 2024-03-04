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
            $table->string('s1', '150')->nullable()->change();
            $table->string('s2', '150')->nullable()->change();
            $table->string('s3', '150')->nullable()->change();
            $table->string('s4', '150')->nullable()->change();
            $table->string('s5', '150')->nullable()->change();

            // remove the original index
            $table->dropIndex('affiliate_tracker_date_unique_index');
            $table->unique(['affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5', 'created_at'], 'subids_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks_vs_registration_statistics', function (Blueprint $table) {
            $table->unique(['affiliate_id', 'revenue_tracker_id', 'created_at'], 'affiliate_tracker_date_unique_index');
        });
    }
};
