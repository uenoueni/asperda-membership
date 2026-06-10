<?php

use App\Jobs\SendPaymentReminderJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new SendPaymentReminderJob)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('SendPaymentReminderJob gagal dijalankan oleh scheduler.');
    });
