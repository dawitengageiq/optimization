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
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->dropColumn(['default_order', 'mixed_coreg_default_order']);
            $table->integer('mixed_coreg_campaign_limit');
            $table->boolean('order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->boolean('default_order');
            $table->boolean('mixed_coreg_default_order');
            $table->dropColumn(['mixed_coreg_campaign_limit', 'order_type']);
        });
    }
};
