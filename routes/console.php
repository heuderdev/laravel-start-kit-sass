<?php

use App\Jobs\ReconcileTenantSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::job(new ReconcileTenantSubscriptions)
    // ->dailyAt('01:00')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('ReconcileTenantSubscriptions falhou.');
    });
