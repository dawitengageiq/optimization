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
            $table->string('phone_number', 75)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_user_request', function (Blueprint $table) {
            $table->dropColumn(['phone_number']);
        });
    }
};
