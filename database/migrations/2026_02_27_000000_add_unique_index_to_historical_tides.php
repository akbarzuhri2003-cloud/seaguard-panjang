<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historical_tides', function (Blueprint $table) {
            $table->unique(['date', 'time'], 'historical_tides_date_time_unique');
        });
    }

    public function down(): void
    {
        Schema::table('historical_tides', function (Blueprint $table) {
            $table->dropUnique('historical_tides_date_time_unique');
        });
    }
};
