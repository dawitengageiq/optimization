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
        Schema::create('advertisers', function (Blueprint $table) {

            $table->increments('id');
            $table->string('company', 100);
            $table->string('website_url', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 25)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 8)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->unsigned();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('advertisers');
    }
};
