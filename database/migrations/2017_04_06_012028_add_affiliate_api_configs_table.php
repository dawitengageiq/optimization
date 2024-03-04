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
            $table->tinyInteger('one_loading')->default(0)->after('display_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_api_configs', function (Blueprint $table) {
            $table->dropColumn('one_loading');
        });
    }
};
