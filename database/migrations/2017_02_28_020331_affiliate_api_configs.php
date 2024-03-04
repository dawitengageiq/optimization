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
        Schema::create('affiliate_api_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index()->nullable();
            $table->string('campaign_type_order', 100)->default('[1, 2, 5]');
            $table->string('excluded_campaign_id', 254)->default('[]');
            $table->integer('display_limit');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('affiliate_api_configs');
    }
};
