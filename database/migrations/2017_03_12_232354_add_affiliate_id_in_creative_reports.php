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
        Schema::table('creative_reports', function (Blueprint $table) {
            $table->integer('affiliate_id')->unsigned()->nullable()->index();
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creative_reports', function (Blueprint $table) {
            $table->dropForeign('creative_reports_affiliate_id_foreign');
            $table->dropColumn(['affiliate_id']);
        });
    }
};
