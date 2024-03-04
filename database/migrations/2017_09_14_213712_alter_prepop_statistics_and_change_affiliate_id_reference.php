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
        Schema::table('prepop_statistics', function (Blueprint $table) {
            $table->dropForeign('prepop_statistics_affiliate_id_foreign');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prepop_statistics', function (Blueprint $table) {
            $table->dropForeign('prepop_statistics_affiliate_id_foreign');
            $table->foreign('affiliate_id')->references('affiliate_id')->on('affiliate_revenue_trackers')->onDelete('cascade');
        });
    }
};
