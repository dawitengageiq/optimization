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
        Schema::table('page_view_statistics', function (Blueprint $table) {
            $table->integer('ads')->default(0)->after('rex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_view_statistics', function (Blueprint $table) {
            $table->dropColumn(['ads']);
        });
    }
};
