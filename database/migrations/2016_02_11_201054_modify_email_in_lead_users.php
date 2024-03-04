<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //SHOW INDEX FROM lead_users
        Schema::table('lead_users', function ($table) {
            $table->dropUnique('lead_users_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_users', function ($table) {
            $table->unique('email');
        });
    }
};
