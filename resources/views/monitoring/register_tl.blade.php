@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Tools (Diberi Action Z-Index agar bisa diklik) -->
    <div class="d-flex justify-content-between align-items-center mb-4 action-header-tools">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-table me-2"></i> WP-RA-07-B | REGISTER TEMUAN DAN TINDAK LANJUT</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Daftar master komprehensif 132 kolom temuan audit, rekomendasi, komitmen, dan status tindak lanjut</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'register_tl') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Master Excel
            </a>
            <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahRegister">
                <i class="bi bi-plus-circle me-1"></i> Tambah Item TL
            </button>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark"><i class="bi bi-list-columns-reverse me-2"></i> Database Master Register Tindak Lanjut (132 Fields)</h6>
            <span class="badge bg-primary">Total Record: {{ count($registers ?? []) }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 11px;">
                    <thead class="table-dark text-center sticky-top">
                        <tr>
                            <!-- 1-10 -->
                            <th class="py-2">No</th>
                            <th class="py-2">TL ID</th>
                            <th class="py-2">No Laporan</th>
                            <th class="py-2">Tanggal Laporan</th>
                            <th class="py-2">Judul Laporan</th>
                            <th class="py-2">Tahun Pemeriksaan</th>
                            <th class="py-2">Jenis Pemeriksaan</th>
                            <th class="py-2">Sumber TL</th>
                            <th class="py-2">Kode Unit Sumber</th>
                            <th class="py-2">Nama Unit Sumber</th>

                            <!-- 11-20 -->
                            <th class="py-2">Tipe Unit</th>
                            <th class="py-2">Wilayah / Kantor Pembina</th>
                            <th class="py-2">No Temuan</th>
                            <th class="py-2">Judul Temuan</th>
                            <th class="py-2">Bidang / Kelompok Temuan</th>
                            <th class="py-3" style="min-width: 250px;">Uraian Temuan Short</th>
                            <th class="py-3" style="min-width: 300px;">Uraian Temuan Full</th>
                            <th class="py-2">Kondisi</th>
                            <th class="py-2">Kriteria</th>
                            <th class="py-2">Sebab</th>

                            <!-- 21-30 -->
                            <th class="py-2">Akibat</th>
                            <th class="py-2">Nilai Temuan Rp</th>
                            <th class="py-2">Mata Uang</th>
                            <th class="py-2">Kategori Temuan</th>
                            <th class="py-2">Sub Kategori Temuan</th>
                            <th class="py-2">Klasifikasi Penyebab</th>
                            <th class="py-2">Dampak Finansial</th>
                            <th class="py-2">Dampak Non Finansial</th>
                            <th class="py-2">Risk Rating Temuan</th>
                            <th class="py-2">Prioritas Pemantauan</th>

                            <!-- 31-40 -->
                            <th class="py-2">No Rekomendasi</th>
                            <th class="py-3" style="min-width: 250px;">Uraian Rekomendasi Short</th>
                            <th class="py-3" style="min-width: 300px;">Uraian Rekomendasi Full</th>
                            <th class="py-2">Jenis Rekomendasi</th>
                            <th class="py-2">Sifat Rekomendasi</th>
                            <th class="py-2">Output Diharapkan</th>
                            <th class="py-2">Kode PIC Utama</th>
                            <th class="py-2">Nama PIC Utama</th>
                            <th class="py-2">Jabatan PIC Utama</th>
                            <th class="py-2">PIC Pendukung 1</th>

                            <!-- 41-50 -->
                            <th class="py-2">PIC Pendukung 2</th>
                            <th class="py-2">Komitmen Auditee</th>
                            <th class="py-2">Rencana Aksi Auditee</th>
                            <th class="py-2">Target Awal Waktu</th>
                            <th class="py-2">Target Berlaku Waktu</th>
                            <th class="py-2">Jumlah Perpanjangan</th>
                            <th class="py-2">Tanggal Target Baru</th>
                            <th class="py-2">Indikator Selesai</th>
                            <th class="py-2">Dokumen Bukti Diharapkan</th>
                            <th class="py-2">Closure Authority Default</th>

                            <!-- 51-60 -->
                            <th class="py-2">Butuh Approver Khusus</th>
                            <th class="py-2">Pejabat Approval Khusus</th>
                            <th class="py-2">Status Awal TL</th>
                            <th class="py-2">Status Current TL</th>
                            <th class="py-2">Status Final TL</th>
                            <th class="py-2">Kategori Status SOP07</th>
                            <th class="py-2">Tanggal Entry Register</th>
                            <th class="py-2">User Entry</th>
                            <th class="py-2">Tanggal Validasi Entry</th>
                            <th class="py-2">Validator Entry</th>

                            <!-- 61-70 -->
                            <th class="py-2">Status Validasi Entry</th>
                            <th class="py-2">Catatan Validasi Entry</th>
                            <th class="py-2">RA Pemantau Utama</th>
                            <th class="py-2">RA Pemantau Pendamping</th>
                            <th class="py-2">Kabag RA Reviewer</th>
                            <th class="py-2">Kadiv SKAI Reviewer</th>
                            <th class="py-2">Frekuensi Monitoring Standard</th>
                            <th class="py-2">Jadwal Reminder T1</th>
                            <th class="py-2">Jadwal Reminder T2</th>
                            <th class="py-2">Jadwal Reminder T3</th>

                            <!-- 71-80 -->
                            <th class="py-2">Reminder Terakhir Send Date</th>
                            <th class="py-2">Status Reminder Current</th>
                            <th class="py-2">Total Reminder Sent</th>
                            <th class="py-2">Flag Auto Reminder Active</th>
                            <th class="py-2">Tanggal Penerimaan Bukti Last</th>
                            <th class="py-2">No Surdok Penerimaan Bukti</th>
                            <th class="py-2">Ringkasan Bukti Diterima Last</th>
                            <th class="py-2">File Bukti Path / URL</th>
                            <th class="py-2">Status Bukti Current</th>
                            <th class="py-2">Jumlah Kali Penolakan Bukti</th>

                            <!-- 81-90 -->
                            <th class="py-2">Tanggal KKA Verifikasi Last</th>
                            <th class="py-2">No KKA Verifikasi Last</th>
                            <th class="py-2">Verifikator RA Last</th>
                            <th class="py-2">Hasil Verifikasi Relevan</th>
                            <th class="py-2">Hasil Verifikasi Lengkap</th>
                            <th class="py-2">Hasil Verifikasi Valid</th>
                            <th class="py-2">Hasil Verifikasi Korektif</th>
                            <th class="py-2">Hasil Verifikasi Testable</th>
                            <th class="py-2">Coverage Verifikasi Percent</th>
                            <th class="py-2">Kesimpulan Verifikasi Last</th>

                            <!-- 91-100 -->
                            <th class="py-2">Usulan Status Dari RA</th>
                            <th class="py-2">Catatan Verifikasi RA</th>
                            <th class="py-2">Tanggal Pengajuan Closure</th>
                            <th class="py-2">No Usulan Closure</th>
                            <th class="py-2">Closure Authority Target</th>
                            <th class="py-2">Tanggal Keputusan Closure</th>
                            <th class="py-2">Pejabat Decision Closure</th>
                            <th class="py-2">Keputusan Closure Final</th>
                            <th class="py-2">No SK / Surat Closure</th>
                            <th class="py-2">Catatan Decision Closure</th>

                            <!-- 101-110 -->
                            <th class="py-2">Flag Reopened</th>
                            <th class="py-2">Tanggal Reopened</th>
                            <th class="py-2">Alasan Reopened</th>
                            <th class="py-2">Total Reopened Count</th>
                            <th class="py-2">Flag Perpanjangan Target</th>
                            <th class="py-2">Alasan Perpanjangan Last</th>
                            <th class="py-2">Pemberi Izin Perpanjangan</th>
                            <th class="py-2">Flag Eskalasi Active</th>
                            <th class="py-2">Level Eskalasi Current</th>
                            <th class="py-2">Tanggal Eskalasi Last</th>

                            <!-- 111-120 -->
                            <th class="py-2">Tujuan Eskalasi Last</th>
                            <th class="py-2">No Surat Eskalasi</th>
                            <th class="py-2">Respons Eskalasi Last</th>
                            <th class="py-2">Aging Target Days</th>
                            <th class="py-2">Aging Days Current</th>
                            <th class="py-2">Aging Overdue Days</th>
                            <th class="py-2">Aging Bucket Group</th>
                            <th class="py-2">Overdue Flag</th>
                            <th class="py-2">Risk Impact Current</th>
                            <th class="py-2">Finansial Recovered Rp</th>

                            <!-- 121-132 -->
                            <th class="py-2">Sisa Exposure Rp</th>
                            <th class="py-2">Keterangan Recovery</th>
                            <th class="py-2">Sebab Unclosed Detail</th>
                            <th class="py-2">Kendala Auditee Detail</th>
                            <th class="py-2">Rencana Tindak Lanjut Sisa</th>
                            <th class="py-2">Link Dokumen Pendukung</th>
                            <th class="py-2">Tagging Isu Strategis</th>
                            <th class="py-2">Audit Trail Created At</th>
                            <th class="py-2">Audit Trail Created By</th>
                            <th class="py-2">Audit Trail Updated At</th>
                            <th class="py-2">Audit Trail Updated By</th>
                            <th class="py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($registers) && count($registers) > 0)
                            @foreach($registers as $index => $item)
                            <tr>
                                <!-- 1-10 -->
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><span class="badge bg-primary px-2 py-1">{{ $item->tl_id ?? '-' }}</span></td>
                                <td>{{ $item->no_laporan ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_laporan ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->judul_laporan ?? '-' }}</td>
                                <td class="text-center">{{ $item->tahun_pemeriksaan ?? '-' }}</td>
                                <td>{{ $item->jenis_pemeriksaan ?? '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $item->sumber_tl ?? '-' }}</span></td>
                                <td>{{ $item->kode_unit_sumber ?? '-' }}</td>
                                <td class="fw-semibold">{{ $item->nama_unit_sumber ?? '-' }}</td>

                                <!-- 11-20 -->
                                <td>{{ $item->tipe_unit ?? '-' }}</td>
                                <td>{{ $item->wilayah_kantor_pembina ?? '-' }}</td>
                                <td>{{ $item->no_temuan ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->judul_temuan ?? '-' }}</td>
                                <td>{{ $item->bidang_kelompok_temuan ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 250px;">{{ $item->uraian_temuan_short ?? $item->uraian_temuan ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 300px;">{{ $item->uraian_temuan_full ?? $item->uraian_temuan ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->kondisi ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->kriteria ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->sebab ?? '-' }}</td>

                                <!-- 21-30 -->
                                <td class="text-wrap">{{ $item->akibat ?? '-' }}</td>
                                <td class="text-end fw-bold">{{ isset($item->nilai_temuan_rp) ? number_format($item->nilai_temuan_rp, 0, ',', '.') : '-' }}</td>
                                <td class="text-center">{{ $item->mata_uang ?? 'IDR' }}</td>
                                <td>{{ $item->kategori_temuan ?? '-' }}</td>
                                <td>{{ $item->sub_kategori_temuan ?? '-' }}</td>
                                <td>{{ $item->klasifikasi_penyebab ?? '-' }}</td>
                                <td>{{ $item->dampak_finansial ?? '-' }}</td>
                                <td>{{ $item->dampak_non_finansial ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-danger px-2 py-1">{{ $item->risk_rating_temuan ?? $item->prioritas ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->prioritas_pemantauan ?? '-' }}</td>

                                <!-- 31-40 -->
                                <td>{{ $item->no_rekomendasi ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 250px;">{{ $item->uraian_rekomendasi_short ?? $item->rekomendasi ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 300px;">{{ $item->uraian_rekomendasi_full ?? $item->rekomendasi ?? '-' }}</td>
                                <td>{{ $item->jenis_rekomendasi ?? '-' }}</td>
                                <td>{{ $item->sifat_rekomendasi ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->output_diharapkan ?? '-' }}</td>
                                <td>{{ $item->kode_pic_utama ?? '-' }}</td>
                                <td>{{ $item->nama_pic_utama ?? $item->pic_unit ?? '-' }}</td>
                                <td>{{ $item->jabatan_pic_utama ?? '-' }}</td>
                                <td>{{ $item->pic_pendukung_1 ?? '-' }}</td>

                                <!-- 41-50 -->
                                <td>{{ $item->pic_pendukung_2 ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->komitmen_auditee ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->rencana_aksi_auditee ?? '-' }}</td>
                                <td class="text-center">{{ $item->target_awal_waktu ?? $item->target_awal ?? '-' }}</td>
                                <td class="text-center">{{ $item->target_berlaku_waktu ?? $item->target_berlaku ?? '-' }}</td>
                                <td class="text-center">{{ $item->jumlah_perpanjangan ?? 0 }}</td>
                                <td class="text-center">{{ $item->tanggal_target_baru ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->indikator_selesai ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->dokumen_bukti_diharapkan ?? $item->bukti_diharapkan ?? '-' }}</td>
                                <td>{{ $item->closure_authority_default ?? $item->closure_authority ?? '-' }}</td>

                                <!-- 51-60 -->
                                <td class="text-center">{{ $item->butuh_approver_khusus ?? 'TIDAK' }}</td>
                                <td>{{ $item->pejabat_approval_khusus ?? '-' }}</td>
                                <td class="text-center">{{ $item->status_awal_tl ?? 'OPEN' }}</td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->status_current_tl ?? $item->status_final ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary px-2 py-1">{{ $item->status_final_tl ?? $item->status_final ?? '-' }}</span></td>
                                <td>{{ $item->kategori_status_sop07 ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_entry_register ?? '-' }}</td>
                                <td>{{ $item->user_entry ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_validasi_entry ?? '-' }}</td>
                                <td>{{ $item->validator_entry ?? '-' }}</td>

                                <!-- 61-70 -->
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->status_validasi_entry ?? 'VALID' }}</span></td>
                                <td>{{ $item->catatan_validasi_entry ?? '-' }}</td>
                                <td>{{ $item->ra_pemantau_utama ?? $item->ra_pemantau ?? '-' }}</td>
                                <td>{{ $item->ra_pemantau_pendamping ?? '-' }}</td>
                                <td>{{ $item->kabag_ra_reviewer ?? $item->reviewer ?? '-' }}</td>
                                <td>{{ $item->kadiv_skai_reviewer ?? '-' }}</td>
                                <td>{{ $item->frekuensi_monitoring_standard ?? $item->frekuensi_monitoring ?? '-' }}</td>
                                <td class="text-center">{{ $item->jadwal_reminder_t1 ?? '-' }}</td>
                                <td class="text-center">{{ $item->jadwal_reminder_t2 ?? '-' }}</td>
                                <td class="text-center">{{ $item->jadwal_reminder_t3 ?? '-' }}</td>

                                <!-- 71-80 -->
                                <td class="text-center">{{ $item->reminder_terakhir_send_date ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->status_reminder_current ?? $item->status_reminder ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->total_reminder_sent ?? 0 }}</td>
                                <td class="text-center">{{ $item->flag_auto_reminder_active ?? 'YA' }}</td>
                                <td class="text-center">{{ $item->tanggal_penerimaan_bukti_last ?? '-' }}</td>
                                <td>{{ $item->no_surdok_penerimaan_bukti ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->ringkasan_bukti_diterima_last ?? '-' }}</td>
                                <td>{{ $item->file_bukti_path ?? '-' }}</td>
                                <td class="text-center">{{ $item->status_bukti_current ?? '-' }}</td>
                                <td class="text-center">{{ $item->jumlah_kali_penolakan_bukti ?? 0 }}</td>

                                <!-- 81-90 -->
                                <td class="text-center">{{ $item->tanggal_kka_verifikasi_last ?? '-' }}</td>
                                <td>{{ $item->no_kka_verifikasi_last ?? '-' }}</td>
                                <td>{{ $item->verifikator_ra_last ?? '-' }}</td>
                                <td class="text-center">{{ $item->hasil_verifikasi_relevan ?? '-' }}</td>
                                <td class="text-center">{{ $item->hasil_verifikasi_lengkap ?? '-' }}</td>
                                <td class="text-center">{{ $item->hasil_verifikasi_valid ?? '-' }}</td>
                                <td class="text-center">{{ $item->hasil_verifikasi_korektif ?? '-' }}</td>
                                <td class="text-center">{{ $item->hasil_verifikasi_testable ?? '-' }}</td>
                                <td class="text-end fw-bold">{{ $item->coverage_verifikasi_percent ?? 0 }}%</td>
                                <td>{{ $item->kesimpulan_verifikasi_last ?? '-' }}</td>

                                <!-- 91-100 -->
                                <td class="text-center">{{ $item->usulan_status_dari_ra ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->catatan_verifikasi_ra ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_pengajuan_closure ?? '-' }}</td>
                                <td>{{ $item->no_usulan_closure ?? '-' }}</td>
                                <td>{{ $item->closure_authority_target ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_keputusan_closure ?? '-' }}</td>
                                <td>{{ $item->pejabat_decision_closure ?? '-' }}</td>
                                <td class="text-center">{{ $item->keputusan_closure_final ?? '-' }}</td>
                                <td>{{ $item->no_sk_surat_closure ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->catatan_decision_closure ?? '-' }}</td>

                                <!-- 101-110 -->
                                <td class="text-center">{{ $item->flag_reopened ?? 'TIDAK' }}</td>
                                <td class="text-center">{{ $item->tanggal_reopened ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->alasan_reopened ?? '-' }}</td>
                                <td class="text-center">{{ $item->total_reopened_count ?? 0 }}</td>
                                <td class="text-center">{{ $item->flag_perpanjangan_target ?? 'TIDAK' }}</td>
                                <td class="text-wrap">{{ $item->alasan_perpanjangan_last ?? '-' }}</td>
                                <td>{{ $item->pemberi_izin_perpanjangan ?? '-' }}</td>
                                <td class="text-center">{{ $item->flag_eskalasi_active ?? 'TIDAK' }}</td>
                                <td class="text-center">{{ $item->level_eskalasi_current ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_eskalasi_last ?? '-' }}</td>

                                <!-- 111-120 -->
                                <td>{{ $item->tujuan_eskalasi_last ?? '-' }}</td>
                                <td>{{ $item->no_surat_eskalasi ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->respons_eskalasi_last ?? '-' }}</td>
                                <td class="text-end">{{ $item->aging_target_days ?? '-' }}</td>
                                <td class="text-end fw-bold">{{ $item->aging_days_current ?? $item->hari_ke_target ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold">{{ $item->aging_overdue_days ?? $item->aging_overdue ?? '-' }}</td>
                                <td class="text-center">{{ $item->aging_bucket_group ?? $item->aging_bucket ?? '-' }}</td>
                                <td class="text-center">{{ $item->overdue_flag ?? 'TIDAK' }}</td>
                                <td>{{ $item->risk_impact_current ?? '-' }}</td>
                                <td class="text-end">{{ isset($item->finansial_recovered_rp) ? number_format($item->finansial_recovered_rp, 0, ',', '.') : '-' }}</td>

                                <!-- 121-132 -->
                                <td class="text-end">{{ isset($item->sisa_exposure_rp) ? number_format($item->sisa_exposure_rp, 0, ',', '.') : '-' }}</td>
                                <td class="text-wrap">{{ $item->keterangan_recovery ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->sebab_unclosed_detail ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->kendala_auditee_detail ?? '-' }}</td>
                                <td class="text-wrap">{{ $item->rencana_tindak_lanjut_sisa ?? '-' }}</td>
                                <td>{{ $item->link_dokumen_pendukung ?? '-' }}</td>
                                <td>{{ $item->tagging_isu_strategis ?? '-' }}</td>
                                <td class="text-center">{{ $item->created_at ?? '-' }}</td>
                                <td>{{ $item->created_by ?? '-' }}</td>
                                <td class="text-center">{{ $item->updated_at ?? '-' }}</td>
                                <td>{{ $item->updated_by ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" title="Detail"><i class="bi bi-eye"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit"><i class="bi bi-pencil"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="132" class="text-center text-muted py-5 fw-semibold">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada data pada Master Register TL.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection