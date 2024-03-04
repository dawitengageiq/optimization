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
        Schema::table('lead_user_request', function (Blueprint $table) {
            $table->tinyInteger('is_sent')->default(0);
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('is_reported')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_user_request', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropColumn(['is_sent', 'is_deleted', 'is_reported']);
        });
    }
};
