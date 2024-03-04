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
        Schema::create('iframe_affiliate_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('lead_count')->default(0);
            $table->float('revenue')->default(0.0);
            $table->date('created_at');

            $table->foreign('affiliate_id')->references('affiliate_id')->on('revenue_tracker_webview_statistics')->onDelete('cascade');
            $table->foreign('revenue_tracker_id')->references('revenue_tracker_id')->on('revenue_tracker_webview_statistics')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('iframe_affiliate_reports');
    }
};
