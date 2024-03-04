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
        Schema::create('affiliate_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->string('affiliate_name', 100)->nullable();
            $table->integer('affiliate_revenue_tracker_id')->unsigned()->index()->nullable();
            $table->string('revenue_tracker_name', 100)->nullable();
            $table->tinyInteger('type');
            $table->integer('clicks')->default(0);
            $table->double('payout', 15, 8)->default(0.0);
            $table->integer('leads')->default(0);
            $table->double('revenue', 15, 8)->default(0.0);
            $table->double('we_get', 15, 8)->default(0.0);
            $table->double('margin', 15, 8)->default(0.0);
            //$table->timestamps();
            $table->date('created_at')->nullable();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            $table->foreign('affiliate_revenue_tracker_id')->references('revenue_tracker_id')->on('affiliate_revenue_trackers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('affiliate_reports');
    }
};
