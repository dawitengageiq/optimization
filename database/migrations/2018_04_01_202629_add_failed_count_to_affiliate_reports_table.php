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
        Schema::table('affiliate_reports', function (Blueprint $table) {
            $table->integer('failed_count')->default(0)->after('reject_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_reports', function (Blueprint $table) {
            $table->dropColumn('failed_count');
        });
    }
};
