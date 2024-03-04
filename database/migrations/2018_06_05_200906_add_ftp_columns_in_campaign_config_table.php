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
            $table->boolean('ftp_sent')->default(0);
            $table->boolean('ftp_protocol')->nullable();
            $table->string('ftp_username', 80)->nullable();
            $table->string('ftp_password', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_configs', function (Blueprint $table) {
            $table->dropColumn(['ftp_sent', 'ftp_protocol', 'ftp_username', 'ftp_password']);
        });
    }
};
