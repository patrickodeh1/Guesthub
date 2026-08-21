<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Task 23: auto-checkout bookings 30 minutes after checkout time if the guest
// never pressed "All Done". Runs frequently so the 30-minute grace period is
// honored reasonably precisely without needing a real-time queue.
Schedule::command('bookings:auto-checkout')->everyFiveMinutes();

// Task 30: "time to check in" alert, sent once per booking on its check-in
// day. Runs early morning so guests get it well before typical check-in
// times; the whereNull(checkin_reminder_sent_at) guard makes re-runs safe.
Schedule::command('bookings:send-checkin-reminders')->dailyAt('08:00');
