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
        Schema::create('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('offer_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->string('s1', 100)->nullable();
            $table->string('s2', 100)->nullable();
            $table->string('s3', 100)->nullable();
            $table->string('s4', 100)->nullable();
            $table->string('s5', 100)->nullable();
            $table->string('note', 500)->nullable();
            $table->string('tracking_link', 500)->nullable();
            $table->integer('crg_limit')->unsigned()->nullable();
            $table->integer('ext_limit')->unsigned()->nullable();
            $table->integer('lnk_limit')->unsigned()->nullable();
            $table->tinyInteger('path_type')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('affiliate_revenue_trackers');
    }
};
