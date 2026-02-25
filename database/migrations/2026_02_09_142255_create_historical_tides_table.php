<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_tides', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time');
            $table->decimal('height', 5, 2); // tinggi dalam meter
            $table->enum('type', ['HIGH_TIDE', 'MEDIUM_TIDE', 'LOW_TIDE']);
            $table->decimal('temperature', 4, 1); // suhu dalam °C
            $table->decimal('wind_speed', 5, 2); // kecepatan angin m/s
            $table->string('wind_direction', 50);
            $table->decimal('pressure', 6, 2); // tekanan hPa
            $table->timestamps();
            
            $table->index(['date', 'time']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_tides');
    }
};