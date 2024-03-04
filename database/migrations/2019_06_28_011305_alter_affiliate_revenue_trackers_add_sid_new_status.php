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
            $table->boolean('new_subid_breakdown_status')->nullable();
            $table->boolean('report_subid_breakdown_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->dropColumn(['new_subid_breakdown_status', 'report_subid_breakdown_status']);
        });
    }
};
