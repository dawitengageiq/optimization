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
            $table->dropUnique('campaign_type_id_campaign_id_unique_key');
            $table->unique(['campaign_type_id', 'revenue_tracker_id', 'campaign_id'], 'campaign_type_id_revenue_tracker_id_campaign_id_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_view_reports', function (Blueprint $table) {
            $table->dropUnique('campaign_type_id_revenue_tracker_id_campaign_id_unique_key');
            $table->unique(['campaign_type_id', 'campaign_id'], 'campaign_type_id_campaign_id_unique_key');
        });
    }
};
