<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelaporanBulanan\Sop08MasterRa;
use App\Models\PelaporanBulanan\Sop08MasterUnit;
use App\Models\User;

class Sop08MasterRaSeeder extends Seeder
{
    public function run(): void
    {
        $units = Sop08MasterUnit::where('is_active', true)
            ->whereNotNull('assigned_ra')
            ->orderBy('kode_unit')
            ->get();

        $counter = 1;
        foreach ($units as $u) {
            $raId = "RA-" . str_pad($counter++, 3, '0', STR_PAD_LEFT);

            // Cari user RA jika ada yang cocok berdasarkan nama atau cabang/unit
            $user = User::where(function($q) use ($u) {
                    $q->where('name', 'like', "%{$u->assigned_ra}%")
                      ->orWhere('email', 'like', "%" . strtolower(str_replace(' ', '', $u->assigned_ra)) . "%");
                })
                ->where('role', 'ra')
                ->first();

            if (!$user) {
                // fallback cari user RA dengan unit_kerja atau cabang
                $user = User::where('role', 'ra')
                    ->where(function($q) use ($u) {
                        $q->where('unit_kerja', $u->kode_unit)
                          ->orWhere('cabang_id', $u->cabang_id);
                    })
                    ->first();
            }

            Sop08MasterRa::updateOrCreate(
                ['kode_kc_penempatan' => $u->kode_unit],
                [
                    'ra_id' => $raId,
                    'user_id' => $user?->id,
                    'nama_ra' => $u->assigned_ra,
                    'kode_kc_penempatan' => $u->kode_unit,
                    'nama_kc' => $u->nama_unit,
                    'unit_ra' => $u->unit_ra ?: 'Bagian Resident Auditor',
                    'status' => 'Aktif',
                    'tanggal_mulai' => '2026-01-01',
                    'catatan' => "Penugasan resmi Resident Auditor {$u->nama_unit} sesuai SK Direksi.",
                ]
            );
        }
    }
}
