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
        Schema::create('campaign_revenue_view_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->integer('campaign_id')->unsigned()->index();
            $table->double('revenue', 15, 8)->index();
            $table->integer('views')->unsigned()->index();
            $table->date('created_at');

            $table->unique(['revenue_tracker_id', 'campaign_id', 'created_at'], 'tracker_campaign_id_date');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('campaign_revenue_view_statistics');
    }
};
