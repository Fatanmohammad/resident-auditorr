@extends('layouts.app')

@section('title', 'Berita Acara Pemeriksaan Kas - ' . $assignment->nama_unit)

@section('content')
<div class="container py-4">
    <!-- Tombol Aksi & Navigasi (Disembunyikan saat cetak) -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <a href="{{ route('surprise_audit.kas', $assignment->visit_id) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Form KKA Kas
            </a>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Cetak Dokumen Resmi (PDF / Printer)
            </button>
        </div>
    </div>

    <!-- Lembar Dokumen Resmi Bank Sulteng (Print Container) -->
    <div class="card border-0 shadow p-5 bg-white print-page">
        <!-- ================================================================= -->
        <!-- DOKUMEN 1: BERITA ACARA PEMERIKSAAN KAS -->
        <!-- ================================================================= -->
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1 tracking-wide text-uppercase" style="letter-spacing: 1.5px;">BERITA ACARA PEMERIKSAAN KAS</h4>
            <h5 class="fw-bold text-uppercase mb-0">PT. BANK SULTENG {{ $assignment->nama_unit }}</h5>
        </div>

        <div class="my-4 lh-lg text-justify" style="font-size: 15px;">
            <p class="mb-3">
                Pada hari ini, <strong>{{ $tglInfo['hari'] }}</strong> {{ $tglInfo['terbilang'] }} 
                (<strong>{{ $tglInfo['angka'] }}</strong>) jam <strong>{{ $assignment->jam_mulai_opname ?? '16.30' }} Wita</strong> 
                {{ $assignment->kondisi_opname ?? 'sebelum Kas Harian dilimpahkan Kepada Teller' }}, kami bertanda tangan dibawah ini:
            </p>

            <div class="ms-4 mb-3">
                <table class="table table-borderless table-sm w-auto mb-0">
                    <tr>
                        <td style="width: 260px;">1. {{ $assignment->pemeriksa_1 ?? $assignment->resident_auditor ?? 'Resident Auditor 1' }}</td>
                        <td style="width: 20px;">:</td>
                        <td class="fw-semibold">Pemeriksa</td>
                    </tr>
                    <tr>
                        <td>2. {{ $assignment->pemeriksa_2 ?? 'Rostika Warda' }}</td>
                        <td>:</td>
                        <td class="fw-semibold">Pemeriksa</td>
                    </tr>
                </table>
            </div>

            <p class="mb-3">
                Telah mengadakan pemeriksaan kas pada <strong>PT. Bank Sulteng {{ $assignment->nama_unit }}</strong> dengan disaksikan oleh:
            </p>

            <div class="ms-4 mb-4">
                <table class="table table-borderless table-sm w-auto mb-0">
                    <tr>
                        <td style="width: 260px;">1. {{ $assignment->saksi_1 ?? 'Hajara Harun' }}</td>
                        <td style="width: 20px;">:</td>
                        <td class="fw-semibold">Pemimpin Seksi Pelayanan</td>
                    </tr>
                    <tr>
                        <td>2. {{ $assignment->saksi_2 ?? 'Jasmawati T. Tolabo' }}</td>
                        <td>:</td>
                        <td class="fw-semibold">Teller</td>
                    </tr>
                </table>
            </div>

            <p class="mb-2 fw-semibold">Dengan hasil sebagai berikut:</p>
            <div class="ms-4 mb-4">
                <table class="table table-borderless table-sm w-auto mb-0" style="font-size: 15px;">
                    <tr>
                        <td style="width: 320px;">I. Jumlah Physic Uang</td>
                        <td style="width: 50px;">: Rp.</td>
                        <td class="text-end fw-bold" style="min-width: 170px;">{{ number_format($totalFisik, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>II. Jumlah Menurut Perincian Kas Olibs</td>
                        <td>: Rp.</td>
                        <td class="text-end fw-bold">{{ number_format($totalSistem, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="border-top">
                        <td>III. Pencocokan Saldo</td>
                        <td>: Rp.</td>
                        <td class="text-end fw-bold {{ $selisih != 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format(abs($selisih), 2, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

            <p class="mb-4">
                Pemeriksaan kas ini berakhir pada jam <strong>{{ $assignment->jam_selesai_opname ?? '18.45' }} Wita</strong> dan 
                @if($selisih > 0)
                    terdapat <span class="fw-bold">kelebihan kas</span> senilai <strong>Rp. {{ number_format($selisih, 0, ',', '.') }}</strong> yang dikarenakan {{ $assignment->keterangan_selisih_kas ?? 'adanya kesalahan perhitungan uang pada Teller' }}.
                @elseif($selisih < 0)
                    terdapat <span class="fw-bold text-danger">kekurangan kas</span> senilai <strong>Rp. {{ number_format(abs($selisih), 0, ',', '.') }}</strong> yang dikarenakan {{ $assignment->keterangan_selisih_kas ?? 'selisih fisik kas' }}.
                @else
                    posisi kas berada dalam kondisi <span class="fw-bold text-success">Nihil / Cocok</span> antara fisik kas dengan sistem Olibs.
                @endif
            </p>
        </div>

        <!-- Tanggal dan Blok Tanda Tangan Berita Acara -->
        <div class="text-end mb-4 me-5" style="font-size: 15px;">
            Palu, {{ date('d F Y', strtotime($assignment->tanggal_rencana)) }}
        </div>

        <div class="row text-center mb-5" style="font-size: 14px;">
            <div class="col-6">
                <p class="fw-bold mb-5">YANG MENYAKSIKAN</p>
                <div class="row pt-4">
                    <div class="col-12 mb-4 text-start ps-5">
                        1. {{ $assignment->saksi_1 ?? 'Hajara Harun' }} : ...................................
                    </div>
                    <div class="col-12 text-start ps-5">
                        2. {{ $assignment->saksi_2 ?? 'Jasmawati T. Tolabo' }} : ...................................
                    </div>
                </div>
            </div>
            <div class="col-6">
                <p class="fw-bold mb-5" style="letter-spacing: 2px;">P E M E R I K S A</p>
                <div class="row pt-4">
                    <div class="col-12 mb-4 text-start ps-5">
                        1. {{ $assignment->pemeriksa_1 ?? $assignment->resident_auditor ?? 'Resident Auditor' }} : ...................................
                    </div>
                    <div class="col-12 text-start ps-5">
                        2. {{ $assignment->pemeriksa_2 ?? 'Rostika Warda' }} : ...................................
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-5 pt-3">
            <p class="mb-1">Mengetahui,</p>
            <p class="fw-bold mb-5 text-uppercase">PT. BANK SULTENG {{ $assignment->nama_unit }}</p>
            <div style="height: 60px;"></div>
            <p class="fw-bold mb-0 text-decoration-underline text-uppercase">{{ $assignment->pejabat_mengetahui ?? 'ADE IRMA S. MAILILI' }}</p>
            <small class="text-muted">Pemimpin Cabang / Pemimpin Cabang Pembantu</small>
        </div>

        <!-- ================================================================= -->
        <!-- DOKUMEN 2 (LAMPIRAN): LEMBAR FORMULIR KEADAAN KAS -->
        <!-- ================================================================= -->
        <div class="page-break" style="page-break-before: always; margin-top: 60px;"></div>

        <div class="text-center mb-4 pt-4 border-top">
            <h4 class="fw-bold mb-1 tracking-wide text-uppercase" style="letter-spacing: 2px;">KEADAAN KAS</h4>
            <div class="d-flex justify-content-between px-3 mt-3 text-start small">
                <div>
                    <div><strong>Tanggal :</strong> {{ date('d F Y', strtotime($assignment->tanggal_rencana)) }}</div>
                    <div><strong>J a m :</strong> {{ $assignment->jam_mulai_opname ?? '16.30 WITA' }}</div>
                </div>
                <div class="text-end">
                    <div><strong>Unit :</strong> {{ $assignment->nama_unit }}</div>
                    <div><strong>Klasifikasi :</strong> Rahasia - Need to Know</div>
                </div>
            </div>
        </div>

        <!-- Tabel 7 Kolom Rincian Keadaan Kas Sesuai Format Asli Bank Sulteng -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered border-dark align-middle text-center mb-0" style="font-size: 12.5px;">
                <thead style="background-color: #c7ecee;">
                    <tr>
                        <th rowspan="2" class="align-middle border-dark" style="width: 140px;">NOMINAL</th>
                        <th colspan="4" class="border-dark text-uppercase">J E N I S</th>
                        <th rowspan="2" class="align-middle border-dark" style="width: 160px;">JUMLAH<br>(KOLOM 3 + 5)</th>
                        <th rowspan="2" class="align-middle border-dark" style="width: 120px;">KET</th>
                    </tr>
                    <tr>
                        <th class="border-dark" style="width: 80px;">LEMBAR</th>
                        <th class="border-dark" style="width: 130px;">JUMLAH (Rp)</th>
                        <th class="border-dark" style="width: 70px;">KPG</th>
                        <th class="border-dark" style="width: 110px;">JUMLAH (Rp)</th>
                    </tr>
                    <tr style="background-color: #dff9fb; font-size: 11px;">
                        <th class="border-dark py-1">1</th>
                        <th class="border-dark py-1">2</th>
                        <th class="border-dark py-1">3</th>
                        <th class="border-dark py-1">4</th>
                        <th class="border-dark py-1">5</th>
                        <th class="border-dark py-1">6</th>
                        <th class="border-dark py-1">7</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- KATEGORI: UANG KERTAS -->
                    <tr class="fw-bold text-start" style="background-color: #f1f2f6;">
                        <td colspan="7" class="border-dark ps-2">Uang Kertas</td>
                    </tr>
                    @foreach($denominasiKertas as $nom => $qty)
                    @php
                        $nomVal = (float)$nom;
                        $jmlNom = $qty * $nomVal;
                    @endphp
                    <tr>
                        <td class="text-end pe-3 border-dark">{{ number_format($nomVal, 2, ',', '.') }}</td>
                        <td class="border-dark">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                        <td class="text-end pe-2 border-dark">{{ $jmlNom > 0 ? number_format($jmlNom, 0, ',', '.') : '-' }}</td>
                        <td class="border-dark">-</td>
                        <td class="border-dark">-</td>
                        <td class="text-end pe-2 border-dark fw-semibold">{{ $jmlNom > 0 ? number_format($jmlNom, 2, ',', '.') : '-' }}</td>
                        <td class="border-dark"></td>
                    </tr>
                    @endforeach
                    <tr class="fw-bold border-dark" style="background-color: #f7f9fa;">
                        <td colspan="5" class="text-end pe-3 border-dark">Subtotal Uang Kertas</td>
                        <td class="text-end pe-2 border-dark text-primary">{{ number_format($subtotalKertas, 2, ',', '.') }}</td>
                        <td class="border-dark"></td>
                    </tr>

                    <!-- KATEGORI: UANG LOGAM -->
                    <tr class="fw-bold text-start" style="background-color: #f1f2f6;">
                        <td colspan="7" class="border-dark ps-2">Uang Logam</td>
                    </tr>
                    @foreach($denominasiLogam as $nom => $kpg)
                    @php
                        $nomVal = (float)$nom;
                        $jmlKpg = $kpg * $nomVal;
                    @endphp
                    <tr>
                        <td class="text-end pe-3 border-dark">{{ number_format($nomVal, 2, ',', '.') }}</td>
                        <td class="border-dark">-</td>
                        <td class="border-dark">-</td>
                        <td class="border-dark">{{ $kpg > 0 ? number_format($kpg, 0, ',', '.') : '-' }}</td>
                        <td class="text-end pe-2 border-dark">{{ $jmlKpg > 0 ? number_format($jmlKpg, 0, ',', '.') : '-' }}</td>
                        <td class="text-end pe-2 border-dark fw-semibold">{{ $jmlKpg > 0 ? number_format($jmlKpg, 2, ',', '.') : '-' }}</td>
                        <td class="border-dark"></td>
                    </tr>
                    @endforeach
                    <tr class="fw-bold border-dark" style="background-color: #f7f9fa;">
                        <td colspan="5" class="text-end pe-3 border-dark">Subtotal Uang Logam</td>
                        <td class="text-end pe-2 border-dark text-primary">{{ number_format($subtotalLogam, 2, ',', '.') }}</td>
                        <td class="border-dark"></td>
                    </tr>

                    <!-- KATEGORI: UANG LUSUH -->
                    <tr class="fw-bold text-start" style="background-color: #f1f2f6;">
                        <td colspan="7" class="border-dark ps-2">Uang Lusuh</td>
                    </tr>
                    @foreach($denominasiLusuh as $nom => $qty)
                    @php
                        $nomVal = (float)$nom;
                        $jmlLusuh = $qty * $nomVal;
                    @endphp
                    <tr>
                        <td class="text-end pe-3 border-dark">{{ number_format($nomVal, 2, ',', '.') }}</td>
                        <td class="border-dark">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                        <td class="text-end pe-2 border-dark">{{ $jmlLusuh > 0 ? number_format($jmlLusuh, 0, ',', '.') : '-' }}</td>
                        <td class="border-dark">-</td>
                        <td class="border-dark">-</td>
                        <td class="text-end pe-2 border-dark fw-semibold">{{ $jmlLusuh > 0 ? number_format($jmlLusuh, 2, ',', '.') : '-' }}</td>
                        <td class="border-dark"></td>
                    </tr>
                    @endforeach
                    <tr class="fw-bold border-dark" style="background-color: #f7f9fa;">
                        <td colspan="5" class="text-end pe-3 border-dark">Subtotal Uang Lusuh</td>
                        <td class="text-end pe-2 border-dark text-primary">{{ number_format($subtotalLusuh, 2, ',', '.') }}</td>
                        <td class="border-dark"></td>
                    </tr>

                    <!-- TOTAL KESELURUHAN FISIK -->
                    <tr class="fw-bold fs-6 border-dark" style="background-color: #dfe6e9;">
                        <td colspan="5" class="text-center text-uppercase border-dark py-2">TOTAL FISIK KAS</td>
                        <td class="text-end pe-2 border-dark py-2 text-dark">{{ number_format($totalFisik, 2, ',', '.') }}</td>
                        <td class="border-dark"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabel Pencocokan Saldo -->
        <div class="row mb-4">
            <div class="col-md-7">
                <table class="table table-bordered border-dark table-sm mb-0" style="font-size: 13px;">
                    <thead style="background-color: #f1f2f6;">
                        <tr>
                            <th colspan="3" class="text-start border-dark ps-2">Pencocokan Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-dark ps-2">Saldo Physic Kas</td>
                            <td class="border-dark text-center" style="width: 40px;">Rp. :</td>
                            <td class="border-dark text-end pe-2 fw-bold" style="width: 170px;">{{ number_format($totalFisik, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="border-dark ps-2">Saldo Menurut Kas Besar Sistem Olibs</td>
                            <td class="border-dark text-center">Rp. :</td>
                            <td class="border-dark text-end pe-2 fw-bold">{{ number_format($totalSistem, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="fw-bold {{ $selisih != 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }}">
                            <td class="border-dark ps-2">Selisih {{ $selisih == 0 ? '(Nihil)' : ($selisih > 0 ? '(Lebih)' : '(Kurang)') }}</td>
                            <td class="border-dark text-center">Rp. :</td>
                            <td class="border-dark text-end pe-2">{{ number_format(abs($selisih), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tanda Tangan Lampiran Keadaan Kas Sesuai Format 8 Saksi & Pemeriksa -->
        <div class="row text-center mt-3" style="font-size: 13px;">
            <div class="col-6">
                <p class="fw-bold mb-3">Yang Menyaksikan :</p>
                <div class="text-start ps-4">
                    @foreach($saksiList as $idx => $saksi)
                    <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-25">
                        <span>{{ $idx + 1 }}. {{ is_string($saksi) ? $saksi : ($saksi['nama'] ?? 'Saksi') }}</span>
                        <span>: .............................</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-6">
                <p class="fw-bold mb-3" style="letter-spacing: 2px;">P e m e r i k s a :</p>
                <div class="text-start ps-4">
                    <div class="d-flex justify-content-between mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <span>1. {{ $assignment->pemeriksa_1 ?? $assignment->resident_auditor ?? 'Resident Auditor' }}</span>
                        <span>: .............................</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <span>2. {{ $assignment->pemeriksa_2 ?? 'Rostika Warda' }}</span>
                        <span>: .............................</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 pt-3">
            <p class="mb-1">Mengetahui</p>
            <p class="fw-bold mb-5 text-uppercase">PT. BANK SULTENG {{ $assignment->nama_unit }}</p>
            <div style="height: 50px;"></div>
            <p class="fw-bold mb-0 text-decoration-underline text-uppercase">{{ $assignment->pejabat_mengetahui ?? 'ADE IRMA S. MAILILI' }}</p>
            <small class="text-muted">Pemimpin Cabang Pembantu</small>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background-color: #fff !important;
        font-family: 'Times New Roman', Times, serif !important;
        color: #000 !important;
    }
    .print-page {
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .d-print-none {
        display: none !important;
    }
    .page-break {
        page-break-before: always !important;
    }
    table {
        border-collapse: collapse !important;
    }
    th, td {
        border-color: #000 !important;
    }
}
</style>
@endsection

