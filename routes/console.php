<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('logs:rotate')->quarterly();
Schedule::command('ai:usage-cost-alert')->hourly();
Schedule::command('site-visitors:monthly-export')->lastDayOfMonth('23:55');
