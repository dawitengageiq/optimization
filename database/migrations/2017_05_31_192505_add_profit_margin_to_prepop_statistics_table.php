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
            $table->decimal('profit_margin', 8, 3)->after('prepop_with_errors_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prepop_statistics', function (Blueprint $table) {
            $table->dropColumn('profit_margin');
        });
    }
};
