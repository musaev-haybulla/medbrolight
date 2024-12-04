<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiRateLimitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('api_rate_limits')->insert([
            'id' => 1,
            'minute_window' => now(),
            'day_window' => now()->setTimezone('UTC')->startOfDay(),
            'minute_requests' => 0,
            'day_requests' => 0
        ]);
    }
}
