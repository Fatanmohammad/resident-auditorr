<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop07RegisterTl extends Model
{
    use HasFactory;

    protected $table = 'sop07_register_tls';

    protected $fillable = [
        'tl_id',
        'source_export_id',
        'sumber_tl',
        'no_referensi',
        'tanggal_temuan',
        'kode_unit',
        'nama_unit_sumber',
        'area',
        'uraian_temuan',
        'rencana_aksi',
        'pic_unit',
        'owner_temuan',
        'target_awal',
        'risk_level',
        'bukti_diharapkan',
        'assigned_ra_sumber',
        'reviewer_sumber',
        'unit_kantor_final',
        'closure_authority',
        'prioritas',
        'target_berlaku',
        'bukti_terakhir',
        'hasil_verifikasi_terakhir',
        'keputusan_closure_terakhir',
        'status_final',
        'hari_ke_target',
        'aging_overdue',
        'aging_bucket',
        'frekuensi_monitoring',
        'jadwal_reminder_berikut',
        'reminder_terakhir',
        'status_reminder',
        'level_eskalasi',
        'status_workflow',
        'kelengkapan',
        'duplicate_key',
        'status_duplikat',
        'ra_pemantau',
        'reviewer',
        'catatan_review',
        // Kolom cadangan / kompatibilitas tambahan
        'nomor_laporan',
        'tanggal_laporan',
        'rekomendasi_temuan',
        'komitmen_auditee',
        'target_penyelesaian',
        'pic_auditee',
        'tanggal_closed_final',
        'catatan_reviewer',
        'exception_aktif',
    ];
}