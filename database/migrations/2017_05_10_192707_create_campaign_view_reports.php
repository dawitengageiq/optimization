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
        Schema::create('campaign_view_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_type_id')->nullable()->index();
            $table->integer('campaign_id')->unsigned()->index();
            $table->integer('total_view_count')->index();
            $table->integer('current_view_count')->index();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('campaign_view_reports');
    }
};
