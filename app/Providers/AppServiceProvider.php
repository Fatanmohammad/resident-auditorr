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

            // Notifikasi Peringatan Alarm SOP 06 (Risk Trigger Visit > 10 Temuan Akumulasi ke Admin)
            try {
                if (strtolower($user->role) === 'admin' && class_exists(\App\Services\RiskTrigger\RiskTriggerService::class) && class_exists(\App\Models\RiskTrigger\RegisterTrigger::class)) {
                    $rtService = app(\App\Services\RiskTrigger\RiskTriggerService::class);
                    $rtAlarms = $rtService->getActiveAlarms($user);
                    foreach ($rtAlarms as $alarm) {
                        if (!empty($alarm['is_followed_up'])) {
                            continue;
                        }
                        $notifs->push([
                            'icon' => 'bi-exclamation-triangle-fill text-danger',
                            'text' => "⚠️ Risk Trigger: Cabang {$alarm['nama_unit']} memiliki {$alarm['count']} temuan (> 10). Segera informasikan untuk pendalaman lebih lanjut.",
                            'url'  => route('risk-trigger.index'),
                            'time' => "Akumulasi {$alarm['count']} Temuan (> 10) • Status: " . ($alarm['status_notifikasi'] ?? 'Perlu Diinfokan'),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error database jika migration belum lengkap
            }

            // Notifikasi Daily Register Workflow: KKA Menunggu Konfirmasi untuk Admin / Reviewer
            try {
                if (in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai', 'pimsie', 'korwas'])) {
                    $waitingFindings = \App\Models\DailyRegister\DailyRegisterFinding::where('review_status', \App\Models\DailyRegister\DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI)
                        ->orWhere(function($q) {
                            $q->whereNull('review_status')->whereNotNull('submitted_at')->where('status_review', '!=', 'Approved');
                        })
                        ->latest('submitted_at')
                        ->take(4)
                        ->get();

                    foreach ($waitingFindings as $wf) {
                        $sheetName = str_replace(['KKA_', '_'], ['', ' '], $wf->source_sheet);
                        $notifs->push([
                            'icon' => 'bi-hourglass-split text-warning',
                            'text' => "KKA {$sheetName} (Unit {$wf->kode_unit}) menunggu konfirmasi verifikasi",
                            'url'  => route('daily-register.kka.index', ['kode_unit' => $wf->kode_unit, 'source_sheet' => $wf->source_sheet]),
                            'time' => $wf->submitted_at ? $wf->submitted_at->diffForHumans() : $wf->updated_at->diffForHumans(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error jika query gagal
            }

            // Notifikasi Daily Register Workflow: KKA Perlu Revisi untuk RA
            try {
                if (strtolower($user->role) === 'ra') {
                    $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                        ? $user->cabangIdYangDapatDiakses() 
                        : [$user->cabang_id];
                    $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code');

                    $revisiFindings = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('kode_unit', $unitCodes)
                        ->where(function($q) {
                            $q->where('review_status', \App\Models\DailyRegister\DailyRegisterFinding::STATUS_PERLU_REVISI)
                              ->orWhere('status_review', 'Revisi');
                        })
                        ->latest('reviewed_at')
                        ->take(4)
                        ->get();

                    foreach ($revisiFindings as $rf) {
                        $sheetName = str_replace(['KKA_', '_'], ['', ' '], $rf->source_sheet);
                        $note = \Illuminate\Support\Str::limit($rf->reviewer_note ?? $rf->catatan_reviewer ?? 'Harap periksa catatan revisi', 40);
                        $notifs->push([
                            'icon' => 'bi-exclamation-triangle-fill text-danger',
                            'text' => "KKA {$sheetName} Perlu Revisi: \"{$note}\"",
                            'url'  => route('daily-register.kka.index', ['kode_unit' => $rf->kode_unit, 'source_sheet' => $rf->source_sheet]),
                            'time' => $rf->reviewed_at ? $rf->reviewed_at->diffForHumans() : $rf->updated_at->diffForHumans(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error jika query gagal
            }

            $view->with('headerNotifs', $notifs->take(12));
        });
    }
}
