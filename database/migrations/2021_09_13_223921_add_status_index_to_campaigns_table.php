<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `campaigns` ADD INDEX `status_index` (`status`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
