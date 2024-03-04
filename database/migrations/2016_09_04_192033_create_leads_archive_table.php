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
        Schema::create('leads_archive', function (Blueprint $table) {
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

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('leads_archive');
    }
};
