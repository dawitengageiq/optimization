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
        Schema::table('lead_users', function (Blueprint $table) {
            $table->index('email');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('zip');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropIndex(['zip']);
            $table->dropIndex(['state']);
        });
    }
};
