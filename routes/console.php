<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Command bawaan Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pendaftaran Scheduler Rekapitulasi Data KKA
// Dijalankan otomatis setiap pergantian hari pada jam 00:01 tengah malam
Schedule::command('kka:aggregate-history')->dailyAt('00:01');