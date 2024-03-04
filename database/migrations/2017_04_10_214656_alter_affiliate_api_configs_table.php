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
        Schema::table('affiliate_api_configs', function (Blueprint $table) {
            $table->string('campaign_type_order', 100)->default('[1, 2, 8, 13]')->change();
            $table->integer('display_limit')->default('20')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_api_configs', function (Blueprint $table) {
            $table->dropColumn(['campaign_type_order', 'display_limit']);
        });
    }
};
