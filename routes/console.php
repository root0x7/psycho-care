<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dispatch mood reminders every hour at the top of the hour
Schedule::command('psychocare:dispatch-mood-reminders')->hourly()->withoutOverlapping();
