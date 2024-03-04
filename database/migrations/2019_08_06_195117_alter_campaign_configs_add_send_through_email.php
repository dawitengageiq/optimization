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
        Schema::table('campaign_configs', function (Blueprint $table) {
            $table->boolean('email_sent')->default(0);
            $table->string('email_to', 250)->nullable();
            $table->string('email_title', 80)->nullable();
            $table->string('email_body', 250)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_configs', function (Blueprint $table) {
            $table->dropColumn(['email_sent', 'email_to', 'email_title', 'email_body']);
        });
    }
};
