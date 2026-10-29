<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;
use App\Models\Cabang;
use App\Models\User;

class Sop08MasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Master Parameter SOP 08
        DB::table('sop08_master_parameters')->truncate();
        DB::table('sop08_master_parameters')->insert([
            'kode_sop' => 'SOP-RA-08',
            'versi_wp' => 'v1',
            'tanggal_cutoff' => '2026-10-14',
            'periode_dashboard' => '2026-09-01',
            'hari_kerja_jatuh_tempo' => 5,
            'reviewer_default' => 'Unit RA/Bagian RA',
            'unit_ra_default' => 'Bagian Resident Auditor',
            'target_quality_score' => 80.00,
            
            // Bobot Quality Score
            'bobot_tepat_waktu' => 20,
            'bobot_kelengkapan' => 20,
            'bobot_akurasi' => 15,
            'bobot_analisis' => 15,
            'bobot_bukti' => 10,
            'bobot_tindak_lanjut' => 10,
            'bobot_eskalasi' => 10,
            'ambang_high_attention' => 60.00,

            // Parameter Kinerja RA
            'bobot_konsistensi' => 40,
            'bobot_penyelesaian' => 30,
            'bobot_kualitas' => 30,
            'ambang_baik_konsisten' => 90.00,
            'ambang_memadai' => 80.00,
            'ambang_perlu_peningkatan' => 60.00,

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Daftar Hari Libur Nasional & Cuti Bersama 2026
        DB::table('sop08_daftar_hari_liburs')->truncate();
        $holidays = [
            ['tanggal' => '2026-01-01', 'keterangan' => 'Tahun Baru 2026 Masehi', 'tahun' => 2026],
            ['tanggal' => '2026-01-16', 'keterangan' => 'Isra Mikraj Nabi Muhammad SAW', 'tahun' => 2026],
            ['tanggal' => '2026-02-17', 'keterangan' => 'Tahun Baru Imlek 2577 Kongzili', 'tahun' => 2026],
            ['tanggal' => '2026-03-20', 'keterangan' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'tahun' => 2026],
            ['tanggal' => '2026-03-21', 'keterangan' => 'Hari Raya Idul Fitri 1447 H', 'tahun' => 2026],
            ['tanggal' => '2026-03-22', 'keterangan' => 'Hari Raya Idul Fitri 1447 H', 'tahun' => 2026],
            ['tanggal' => '2026-03-23', 'keterangan' => 'Cuti Bersama Idul Fitri 1447 H', 'tahun' => 2026],
            ['tanggal' => '2026-03-24', 'keterangan' => 'Cuti Bersama Idul Fitri 1447 H', 'tahun' => 2026],
            ['tanggal' => '2026-04-03', 'keterangan' => 'Wafat Yesus Kristus', 'tahun' => 2026],
            ['tanggal' => '2026-05-01', 'keterangan' => 'Hari Buruh Internasional', 'tahun' => 2026],
            ['tanggal' => '2026-05-14', 'keterangan' => 'Kenaikan Yesus Kristus', 'tahun' => 2026],
            ['tanggal' => '2026-05-28', 'keterangan' => 'Hari Raya Idul Adha 1447 H', 'tahun' => 2026],
            ['tanggal' => '2026-05-31', 'keterangan' => 'Hari Raya Waisak 2570 BE', 'tahun' => 2026],
            ['tanggal' => '2026-06-01', 'keterangan' => 'Hari Lahir Pancasila', 'tahun' => 2026],
            ['tanggal' => '2026-06-16', 'keterangan' => 'Tahun Baru Islam 1448 H', 'tahun' => 2026],
            ['tanggal' => '2026-08-17', 'keterangan' => 'Hari Kemerdekaan RI', 'tahun' => 2026],
            ['tanggal' => '2026-08-25', 'keterangan' => 'Maulid Nabi Muhammad SAW', 'tahun' => 2026],
            ['tanggal' => '2026-10-05', 'keterangan' => 'Hari Libur Khusus / Contoh Cuti Bersama', 'tahun' => 2026],
            ['tanggal' => '2026-12-25', 'keterangan' => 'Hari Raya Natal', 'tahun' => 2026],
        ];

        foreach ($holidays as $h) {
            DB::table('sop08_daftar_hari_liburs')->insert([
                'tanggal' => $h['tanggal'],
                'keterangan' => $h['keterangan'],
                'tahun' => $h['tahun'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Seed Master Unit SOP 08 (40 Unit Bank Sulteng)
        DB::table('sop08_master_units')->truncate();

        $units = Unit::with(['raAssignment.primaryRa', 'cabang'])
            ->whereIn('unit_type', ['KC', 'KCU', 'KCP', 'KCPLK'])
            ->where('unit_code', '!=', '000')
            ->orderBy('unit_code')
            ->get();

        foreach ($units as $unit) {
            $assignedRaName = $unit->raAssignment?->primaryRa?->ra_name ?? 'Resident Auditor';
            $userRa = User::where('name', 'like', "%{$assignedRaName}%")->first();
            
            // Cari kode induk jika KCP / KCPLK
            $parentOffice = $unit->parent_office;
            $kodeInduk = null;
            if ($parentOffice) {
                $parentUnit = Unit::where('unit_name', 'like', "%{$parentOffice}%")
                    ->orWhere('base_ra_unit', 'like', "%{$parentOffice}%")
                    ->first();
                $kodeInduk = $parentUnit?->unit_code;
            }

            DB::table('sop08_master_units')->insert([
                'kode_unit' => str_pad($unit->unit_code, 3, '0', STR_PAD_LEFT),
                'nama_unit' => $unit->unit_name,
                'jenis_unit' => $unit->unit_type,
                'kantor_induk' => $parentOffice ?: 'KANTOR PUSAT',
                'kode_kc_induk' => $kodeInduk ?: '001',
                'assigned_ra' => $assignedRaName,
                'unit_ra' => 'Bagian Resident Auditor',
                'cabang_id' => $unit->cabang_id,
                'unit_id' => $unit->id,
                'user_ra_id' => $userRa?->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
