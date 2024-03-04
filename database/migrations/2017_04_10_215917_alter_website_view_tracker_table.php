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
        Schema::table('websites_view_tracker', function (Blueprint $table) {
            $table->string('email', 50)->index()->change();
            $table->string('status', 50)->default('active')->index()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites_view_tracker', function (Blueprint $table) {
            $table->dropColumn(['email', 'status']);
        });
    }
};
