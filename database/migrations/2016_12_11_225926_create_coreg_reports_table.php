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
        Schema::create('coreg_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index()->nullable();
            // $table->string('campaign_name',100);
            $table->double('cost', 15, 8)->default(0.0);
            $table->double('revenue', 15, 8)->default(0.0);
            // $table->double('we_get',15,8)->default(0.0);
            $table->integer('lf_total')->nullable();
            $table->integer('lf_filter_do')->nullable();
            $table->integer('lf_admin_do')->nullable();
            $table->integer('lf_nlr_do')->nullable();
            $table->integer('lr_total')->nullable();
            $table->integer('lr_rejected')->nullable();
            $table->integer('lr_failed')->nullable();
            $table->integer('lr_pending')->nullable();
            $table->integer('lr_cap')->nullable();
            $table->boolean('olr_only')->nullable();
            $table->boolean('source')->nullable();
            $table->date('date')->nullable();

            // $table->timestamps();

            // $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            // $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            // $table->foreign('revenue_tracker_id')->references('revenue_tracker_id')->on('affiliate_revenue_trackers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('coreg_reports');
    }
};
