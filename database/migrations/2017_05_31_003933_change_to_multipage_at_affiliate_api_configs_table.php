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
            $table->renameColumn('one_loading', 'multi_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_api_configs', function (Blueprint $table) {
            $table->dropColumn('multi_page');
        });
    }
};
