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
        Schema::create('cake_conversions', function (Blueprint $table) {

            $table->integer('id')->primary()->unsigned()->index();
            $table->integer('visitor_id')->unsigned()->index();
            $table->integer('request_session_id')->unsigned()->index();
            $table->integer('click_request_session_id')->unsigned()->index();
            $table->integer('click_id')->unsigned()->index();
            $table->date('conversion_date')->nullable();
            $table->date('last_updated')->nullable();
            $table->date('click_date')->nullable();
            $table->integer('event_id')->unsigned()->index();
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('advertiser_id')->unsigned()->index();
            $table->integer('offer_id')->unsigned()->index();
            $table->string('offer_name', 100)->nullable();
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('creative_id')->unsigned()->index();
            $table->string('sub_id_1', 255)->nullable();
            $table->string('sub_id_2', 255)->nullable();
            $table->string('sub_id_3', 255)->nullable();
            $table->string('sub_id_4', 255)->nullable();
            $table->string('sub_id_5', 255)->index()->nullable();
            $table->string('conversion_ip_address', 16)->nullable();
            $table->string('click_ip_address', 16)->nullable();
            $table->double('received_amount', 8, 3)->default(0.0);
            $table->boolean('test');
            $table->string('transaction_id', 255)->nullable();
            $table->timestamps();

            $table->unique(['sub_id_5', 'offer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('cake_conversions');
    }
};
