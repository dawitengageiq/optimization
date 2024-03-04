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
        Schema::create('campaign_no_tracker_archives', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->index();
            $table->string('email', 255)->index();
            $table->integer('count')->unsigned()->default(1);
            $table->string('last_session', 255);
            $table->timestamps();
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');

            $table->unique(['campaign_id', 'email'], 'campaign_no_trackers_campaign_id_email_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('campaign_no_tracker_archives');
    }
};
