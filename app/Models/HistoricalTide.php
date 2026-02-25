<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalTide extends Model
{
    protected $table = 'historical_tides';
    
    protected $fillable = [
        'date', 'time', 'height', 'type',
        'temperature', 'wind_speed', 'wind_direction', 'pressure'
    ];
    
    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
        'height' => 'decimal:2',
        'temperature' => 'decimal:1',
        'wind_speed' => 'decimal:2',
        'pressure' => 'decimal:2',
    ];
}