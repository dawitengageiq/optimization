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
        Schema::create('path_speeds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path', 25);
            $table->float('sum_time')->default(0.0);
            $table->float('avg_time')->default(0.0);
            $table->float('up_time')->default(0.0);
            $table->date('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('path_speeds');
    }
};
