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
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->string('mixed_coreg_recurrence')->default('views');
            $table->time('mixed_coreg_daily')->default('00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->dropColumn(['mixed_coreg_recurrence', 'mixed_coreg_daily']);
        });
    }
};
