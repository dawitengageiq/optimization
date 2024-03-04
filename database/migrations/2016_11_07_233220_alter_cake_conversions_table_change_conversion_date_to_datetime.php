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
        Schema::table('cake_conversions', function (Blueprint $table) {
            $table->dateTime('conversion_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cake_conversions', function (Blueprint $table) {
            $table->date('conversion_date')->change();
        });
    }
};
