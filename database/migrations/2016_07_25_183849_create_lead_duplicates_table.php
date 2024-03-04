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
        Schema::create('lead_duplicates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('affiliate_id')->unsigned()->index();
            $table->string('s1', 100)->nullable();
            $table->string('s2', 100)->nullable();
            $table->string('s3', 100)->nullable();
            $table->string('s4', 100)->nullable();
            $table->string('s5', 100)->nullable();
            $table->tinyInteger('lead_status')->unsigned();
            $table->string('lead_email', 255);
            $table->float('received')->unsigned();
            $table->float('payout')->unsigned();
            $table->integer('retry_count')->unsigned()->nullable();
            $table->date('last_retry_date')->nullable();
            $table->timestamps();

            //$table->unique(['lead_email','campaign_id'],'leads_lead_email_campaign_id_unique_key');
            $table->index(['lead_email', 'campaign_id'], 'leads_lead_email_campaign_id_index');
            $table->index(['campaign_id', 'affiliate_id'], 'leads_campaign_id_affiliate_id_index');
            $table->index('lead_status', 'leads_lead_status_index');

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('lead_duplicates');
    }
};
