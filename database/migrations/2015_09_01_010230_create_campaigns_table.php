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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->integer('advertiser_id')->unsigned()->index();
            $table->tinyInteger('status')->unsigned();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('image', 255)->nullable();
            $table->tinyInteger('lead_cap_type')->unsigned();
            $table->integer('lead_cap_value')->unsigned()->nullable();
            $table->float('default_received')->unsigned();
            $table->float('default_payout')->unsigned();
            $table->tinyInteger('priority')->unsigned()->nullable();
            $table->tinyInteger('campaign_type')->unsigned();
            $table->timestamps();

            $table->foreign('advertiser_id')->references('id')->on('advertisers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('campaigns');
    }
};
