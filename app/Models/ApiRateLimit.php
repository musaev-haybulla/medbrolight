<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRateLimit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'minute_window',
        'day_window',
        'minute_requests',
        'day_requests',
    ];

    protected $casts = [
        'minute_window' => 'datetime',
        'day_window' => 'date',
    ];
}
