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
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('deal')->nullable();
            $table->string('description')->nullable();
            $table->text('fields')->nullable();
            $table->text('additional_fields')->nullable();
            $table->text('rules')->nullable();
            $table->string('sticker')->nullable();
            $table->string('cpa_creative_id')->nullable();
        });
    }

    /**xt
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->dropColumn(['name', 'deal', 'description', 'fields', 'additional_fields', 'rules', 'sticker', 'cpa_creative_id']);
        });
    }
};
