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
        Schema::create('campaign_rejection_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->index();
            // $table->integer('revenue_tracker_id')->nullable();
            // $table->integer('affiliate_id')->unsigned()->index();
            // $table->float('rejection_rate')->default(0.0);
            $table->integer('total_count')->default(0);
            $table->integer('reject_count')->default(0);
            $table->integer('acceptable_reject_count')->default(0);
            $table->integer('duplicate_count')->default(0);
            $table->integer('filter_count')->default(0);
            $table->integer('prepop_count')->default(0);
            $table->integer('other_count')->default(0);
            // $table->boolean('duplicate_only')->default(0);
            $table->date('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('campaign_rejection_statistics');
    }
};
