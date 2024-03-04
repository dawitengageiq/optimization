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
            $table->integer('creative_id')->index()->unsigned()->nullable()->after('affiliate_id');
            $table->foreign('creative_id')->references('id')->on('campaign_creatives')->onDelete('set null');

            $table->integer('path_id')->index()->unsigned()->nullable()->after('creative_id');
            $table->foreign('path_id')->references('id')->on('paths')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_views', function (Blueprint $table) {
            $table->dropForeign('campaign_views_creative_id_foreign');
            $table->dropColumn('creative_id');
            $table->dropForeign('campaign_views_path_id_foreign');
            $table->dropColumn('path_id');
        });
    }
};
