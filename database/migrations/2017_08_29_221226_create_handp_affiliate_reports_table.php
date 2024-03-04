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
        Schema::create('handp_affiliate_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('campaign_id')->unsigned()->index();
            $table->string('s1', 100)->nullable();
            $table->integer('lead_count')->default(0);
            $table->double('received', 15, 8)->default(0.0);
            $table->double('payout', 15, 8)->default(0.0);
            $table->date('created_at');

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('handp_affiliate_reports');
    }
};
