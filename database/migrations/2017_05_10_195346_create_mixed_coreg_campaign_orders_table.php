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
        Schema::create('mixed_coreg_campaign_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('revenue_tracker_id')->unsigned()->nullable()->index();
            $table->text('campaign_id_order');
            $table->dateTime('reorder_reference_date');

            $table->foreign('revenue_tracker_id')->references('revenue_tracker_id')->on('affiliate_revenue_trackers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('mixed_coreg_campaign_orders');
    }
};
