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
        Schema::create('revenue_tracker_webview_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->integer('website_campaign_id')->unsigned()->index(); //this is the campaign_id of revenue tracker and website_id of affiliate_websites
            $table->integer('passovers')->unsigned()->default(0);
            $table->float('payout')->unsigned()->default(0.0);
            $table->date('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('revenue_tracker_webview_statistics');
    }
};
