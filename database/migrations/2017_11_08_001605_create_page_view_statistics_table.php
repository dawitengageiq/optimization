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
        Schema::create('page_view_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->integer('lp')->default(0);
            $table->integer('rp')->default(0);
            $table->integer('to1')->default(0);
            $table->integer('to2')->default(0);
            $table->integer('mo1')->default(0);
            $table->integer('mo2')->default(0);
            $table->integer('mo3')->default(0);
            $table->integer('mo4')->default(0);
            $table->integer('lfc1')->default(0);
            $table->integer('lfc2')->default(0);
            $table->integer('tbr1')->default(0);
            $table->integer('pd')->default(0);
            $table->integer('tbr2')->default(0);
            $table->integer('iff')->default(0);
            $table->integer('rex')->default(0);
            $table->integer('cpawall')->default(0);
            $table->integer('exitpage')->default(0);
            $table->date('created_at');

            $table->unique(['affiliate_id', 'revenue_tracker_id', 'created_at'], 'affiliate_tracker_date_unique_index');

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            $table->foreign('revenue_tracker_id')->references('id')->on('affiliates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('page_view_statistics');
    }
};
