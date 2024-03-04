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
        Schema::table('campaign_view_reports', function (Blueprint $table) {

            $table->integer('revenue_tracker_id')->unsigned()->nullable()->index()->after('current_view_count');
            $table->foreign('revenue_tracker_id')->references('revenue_tracker_id')->on('affiliate_revenue_trackers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_view_reports', function (Blueprint $table) {

            $table->dropForeign('campaign_view_reports_revenue_tracker_id_foreign');
            $table->dropColumn('revenue_tracker_id');

        });
    }
};
