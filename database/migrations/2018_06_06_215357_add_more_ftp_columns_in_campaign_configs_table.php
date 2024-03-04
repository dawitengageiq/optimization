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
            $table->string('ftp_host', 150)->nullable();
            $table->integer('ftp_port')->unsigned()->nullable();
            $table->integer('ftp_timeout')->unsigned()->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_configs', function (Blueprint $table) {
            $table->dropColumn(['ftp_host', 'ftp_port', 'ftp_timeout']);
        });
    }
};
