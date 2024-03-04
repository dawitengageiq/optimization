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
        Schema::create('lead_counts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('affiliate_id')->unsigned()->index()->nullable();
            $table->integer('count')->default(0);

            /*
            $table->integer('daily_count')->default(0);
            $table->integer('weekly_count')->default(0);
            $table->integer('monthly_count')->default(0);
            $table->integer('yearly_count')->default(0);
            */
            $table->timestamp('reference_date')->nullable()->default(null);

            /*
            $table->timestamp('daily_reference_date')->nullable()->default(null);
            $table->timestamp('weekly_reference_date')->nullable()->default(null);
            $table->timestamp('monthly_reference_date')->nullable()->default(null);
            $table->timestamp('yearly_reference_date')->nullable()->default(null);
            */

            $table->index(['affiliate_id', 'campaign_id'], 'leads_affiliate_id_campaign_id_index');

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('lead_counts');
    }
};
