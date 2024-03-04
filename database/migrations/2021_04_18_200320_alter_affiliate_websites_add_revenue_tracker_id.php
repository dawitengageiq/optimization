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
        Schema::table('affiliate_websites', function (Blueprint $table) {
            $table->integer('revenue_tracker_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_websites', function (Blueprint $table) {
            $table->dropColumn('revenue_tracker_id');
        });
    }
};
