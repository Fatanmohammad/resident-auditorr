<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.header', function ($view) {
            if (!auth()->check()) return;
            $user = auth()->user();
            $notifs = collect();

            if ($user->role === 'kabag_ra') {
                \App\Models\AuditPlan::where('status_approval', 'waiting_kabag_approval')
                    ->latest()->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-calendar-check',
                        'text' => 'Audit Plan #' . $p->id . ' menunggu persetujuan Anda',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
            }

            if ($user->role === 'kadiv_skai') {
                \App\Models\AuditPlan::where('status_approval', 'waiting_kadiv_approval')
                    ->latest()->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-calendar-check',
                        'text' => 'Audit Plan #' . $p->id . ' menunggu final approval',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
            }

            if ($user->role === 'ra') {
                \App\Models\AuditPlan::where('ra_user_id', $user->id)
                    ->where('status_approval', 'rejected')
                    ->latest()->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-x-circle',
                        'text' => 'Audit Plan #' . $p->id . ' ditolak',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
            }

            if ($user->role === 'pimsie') {
                \App\Models\AuditPlan::where('status_approval', 'rejected')
                    ->latest()->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-x-circle',
                        'text' => 'Audit Plan #' . $p->id . ' ditolak — perlu diperbaiki',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
                \App\Models\AuditPlan::where('status_approval', 'approved')
                    ->latest()->take(3)->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-check-circle',
                        'text' => 'Audit Plan #' . $p->id . ' telah disetujui',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
            }

            // Notifikasi Peringatan Alarm SOP 06 (Risk Trigger Visit > 5 Temuan Akumulasi)
            try {
                if (class_exists(\App\Services\RiskTrigger\RiskTriggerService::class) && class_exists(\App\Models\RiskTrigger\RegisterTrigger::class)) {
                    $rtService = app(\App\Services\RiskTrigger\RiskTriggerService::class);
                    $rtAlarms = $rtService->getActiveAlarms($user);
                    foreach ($rtAlarms as $alarm) {
                        $notifs->push([
                            'icon' => 'bi-exclamation-octagon-fill text-danger',
                            'text' => "ALARM SOP 06: {$alarm['area']} di {$alarm['nama_unit']} ({$alarm['count']} temuan)",
                            'url'  => route('risk-trigger.register', ['kode_unit' => $alarm['kode_unit'], 'area' => $alarm['area']]),
                            'time' => "Akumulasi > 5 Temuan (DR: {$alarm['dr_count']}, Off: {$alarm['off_count']}, On: {$alarm['on_count']})",
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error database jika migration belum lengkap
            }

            $view->with('headerNotifs', $notifs->take(12));
        });
    }
}
