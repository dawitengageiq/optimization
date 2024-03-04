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
        Schema::table('campaign_views', function (Blueprint $table) {
            $table->unique(['campaign_id', 'session'], 'campaign_views_campaign_id_session_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_views', function (Blueprint $table) {
            $table->dropUnique('campaign_views_campaign_id_session_unique_key');
        });
    }
};
