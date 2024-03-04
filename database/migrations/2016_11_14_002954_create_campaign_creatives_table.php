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
        Schema::create('campaign_creatives', function (Blueprint $table) {
            $table->increments('id');
            $table->double('weight', 5, 3);
            $table->integer('campaign_id')->unsigned()->index();
            $table->string('image', 255);
            $table->text('description');
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('campaign_creatives');
    }
};
