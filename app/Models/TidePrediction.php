<?php
// app/Models/TidePrediction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TidePrediction extends Model
{
    protected $fillable = [
        'date', 'time', 'height', 'type', 
        'temperature', 'wind_speed', 'wind_direction', 'pressure'
    ];

    protected $casts = [
        'date' => 'date',
        'height' => 'decimal:2',
        'temperature' => 'decimal:1',
        'wind_speed' => 'decimal:2',
        'pressure' => 'decimal:2',
    ];
}