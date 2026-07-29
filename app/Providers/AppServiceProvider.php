<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.header', function ($view) {
            if (!auth()->check()) return;
            $user = auth()->user();
            $notifs = collect();

            if ($user->role === 'kabag_ra') {
                \App\Models\AuditPlan::where('status_approval', 'waiting_kabag_approval')
                    ->with('cabang')->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-calendar-check',
                        'text' => 'Audit Plan ' . ($p->cabang?->nama_cabang ?? '-') . ' menunggu persetujuan Anda',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
                \App\Models\KertasKerjaAudit::where('status_kka', 'submitted')
                    ->with('auditPlan.cabang')->get()
                    ->each(fn($k) => $notifs->push([
                        'icon' => 'bi-journal-text',
                        'text' => 'KKA ' . $k->bidang_audit . ' menunggu review',
                        'url'  => route('kka.show', $k->id),
                        'time' => $k->updated_at->diffForHumans(),
                    ]));
            }

            if ($user->role === 'kadiv_skai') {
                \App\Models\AuditPlan::where('status_approval', 'waiting_kadiv_approval')
                    ->with('cabang')->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-calendar-check',
                        'text' => 'Audit Plan ' . ($p->cabang?->nama_cabang ?? '-') . ' menunggu final approval',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
                \App\Models\KertasKerjaAudit::where('status_kka', 'reviewed_kabag')
                    ->with('auditPlan.cabang')->get()
                    ->each(fn($k) => $notifs->push([
                        'icon' => 'bi-journal-text',
                        'text' => 'KKA ' . $k->bidang_audit . ' menunggu approval Kadiv',
                        'url'  => route('kka.show', $k->id),
                        'time' => $k->updated_at->diffForHumans(),
                    ]));
            }

            if ($user->role === 'ra') {
                \App\Models\AuditPlan::where('ra_user_id', $user->id)
                    ->where('status_approval', 'rejected')
                    ->with('cabang')->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-x-circle',
                        'text' => 'Audit Plan ' . ($p->cabang?->nama_cabang ?? '-') . ' ditolak',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
                \App\Models\KertasKerjaAudit::where('status_kka', 'revisi')
                    ->whereHas('auditPlan', fn($q) => $q->where('ra_user_id', $user->id))
                    ->get()
                    ->each(fn($k) => $notifs->push([
                        'icon' => 'bi-pencil-square',
                        'text' => 'KKA ' . $k->bidang_audit . ' perlu direvisi',
                        'url'  => route('kka.show', $k->id),
                        'time' => $k->updated_at->diffForHumans(),
                    ]));
            }

            if ($user->role === 'pimsie') {
                \App\Models\AuditPlan::where('status_approval', 'rejected')
                    ->with('cabang')->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-x-circle',
                        'text' => 'Audit Plan ' . ($p->cabang?->nama_cabang ?? '-') . ' ditolak — perlu diperbaiki',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
                \App\Models\AuditPlan::where('status_approval', 'approved')
                    ->with('cabang')->latest()->take(3)->get()
                    ->each(fn($p) => $notifs->push([
                        'icon' => 'bi-check-circle',
                        'text' => 'Audit Plan ' . ($p->cabang?->nama_cabang ?? '-') . ' telah disetujui',
                        'url'  => route('audit-plan.show', $p->id),
                        'time' => $p->updated_at->diffForHumans(),
                    ]));
            }

            $view->with('headerNotifs', $notifs->take(8));
        });
    }
}
