@extends('layouts.app')

@section('title', 'Rekonsiliasi Persediaan Kartu ATM & PIN Mailer - ' . $assignment->nama_unit)

@section('content')
<div class="container py-4">
    <!-- Tombol Navigasi & Cetak (Disembunyikan saat Print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <a href="{{ route('surprise_audit.kartu', $assignment->visit_id) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Form KKA Kartu
            </a>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Cetak Rekonsiliasi Resmi (PDF / Printer)
            </button>
        </div>
    </div>

    <!-- Lembar Cetak Dokumen Rekonsiliasi Kartu ATM & PIN Mailer -->
    <div class="card border-0 shadow p-5 bg-white print-page">
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1 tracking-wide text-uppercase" style="letter-spacing: 1.5px;">LEMBAR KERJA PEMERIKSAAN REKONSILIASI KARTU ATM & PIN MAILER</h4>
            <h5 class="fw-bold text-uppercase mb-0">PT. BANK SULTENG {{ $assignment->nama_unit }}</h5>
        </div>

        <!-- Header Metadata Bank Sulteng -->
        <div class="row mb-4" style="font-size: 14px;">
            <div class="col-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td style="width: 170px;" class="fw-bold">CABANG</td>
                        <td style="width: 20px;">:</td>
                        <td class="fw-bold text-uppercase">{{ $assignment->nama_unit }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">PERIODE AUDIT</td>
                        <td>:</td>
                        <td>{{ $kartuData->first()->periode_audit ?? ('SEMESTER 2 ' . date('Y')) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">PERIODE DATA</td>
                        <td>:</td>
                        <td>{{ $kartuData->first()->periode_data ?? ('SEMESTER 1 ' . date('Y')) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td style="width: 170px;" class="fw-bold">PROGRAM AUDIT</td>
                        <td style="width: 20px;">:</td>
                        <td class="fw-bold text-uppercase">KARTU ATM & PIN MAILER</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Posisi Rekonsiliasi</td>
                        <td>:</td>
                        <td>{{ date('d/m/Y', strtotime($kartuData->first()->posisi_rekonsiliasi ?? $assignment->tanggal_rencana)) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Kerahasiaan</td>
                        <td>:</td>
                        <td><span class="text-danger fw-bold">Rahasia - Need to Know</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- TABEL 1: FISIK VS SALDO PERSEDIAAN (BUKU BESAR/GL) VS SISTEM CARDMAN -->
        <h6 class="fw-bold text-uppercase mb-2 text-dark border-bottom pb-1">
            1. Rekonsiliasi Fisik vs Saldo Persediaan (Buku/GL)
        </h6>
        <div class="table-responsive mb-3">
            <table class="table table-bordered border-dark align-middle text-center mb-0" style="font-size: 13px;">
                <thead style="background-color: #dff9fb;">
                    <tr>
                        <th rowspan="2" class="border-dark align-middle text-start ps-3" style="width: 160px;">ITEM</th>
                        <th rowspan="2" class="border-dark align-middle" style="width: 120px;">HARGA MODAL</th>
                        <th colspan="2" class="border-dark">JUMLAH FISIK</th>
                        <th rowspan="2" class="border-dark align-middle" style="width: 180px;">SALDO AKHIR PERSEDIAAN (GL)</th>
                        <th rowspan="2" class="border-dark align-middle" style="width: 140px;">SELISIH</th>
                    </tr>
                    <tr>
                        <th class="border-dark" style="width: 90px;">QTY</th>
                        <th class="border-dark" style="width: 140px;">NOMINAL (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kartuData as $row)
                    @php
                        $hargaModal = (float)$row->harga_per_kartu;
                        $nominalFisik = (int)$row->jumlah_fisik_kartu * $hargaModal;
                    @endphp
                    <tr>
                        <td class="text-start ps-3 fw-bold border-dark">KARTU {{ strtoupper($row->jenis_kartu) }}</td>
                        <td class="text-end pe-3 border-dark">{{ number_format($hargaModal, 0, ',', '.') }}</td>
                        <td class="border-dark">{{ $row->jumlah_fisik_kartu > 0 ? number_format($row->jumlah_fisik_kartu, 0, ',', '.') : '-' }}</td>
                        <td class="text-end pe-3 border-dark">{{ $nominalFisik > 0 ? number_format($nominalFisik, 0, ',', '.') : '-' }}</td>
                        <td class="border-dark"></td>
                        <td class="border-dark"></td>
                    </tr>
                    @endforeach
                    <tr class="fw-bold fs-7 border-dark" style="background-color: #f1f2f6;">
                        <td colspan="2" class="text-center text-uppercase border-dark py-2">TOTAL</td>
                        <td class="border-dark py-2">{{ number_format($totalFisikQty, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 border-dark py-2 text-primary">{{ number_format($totalNominalFisik, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 border-dark py-2">{{ number_format($saldoPersediaanGl, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 border-dark py-2 {{ $selisihPersediaanGl != 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($selisihPersediaanGl, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Findings Tabel 1 -->
        <div class="p-3 mb-4 rounded bg-light border border-secondary border-opacity-25" style="font-size: 13.5px;">
            <p class="fw-bold mb-1 text-danger">Findings / Temuan Posisi Saldo Persediaan:</p>
            <p class="mb-0 text-justify lh-base">
                {{ $findings1 ?: ("Ditemukan bahwa posisi antara fisik, saldo persediaan dan sistem cardman berbeda; dimana fisik mencatat sebanyak {$totalFisikQty} pcs atau Rp " . number_format($totalNominalFisik, 0, ',', '.') . "; rekening persediaan Rp " . number_format($saldoPersediaanGl, 0, ',', '.') . " dan sistem mencatat {$cardmanQty} atau Rp " . number_format($cardmanNominal, 0, ',', '.') . ".") }}
            </p>
        </div>

        <!-- TABEL 2: FISIK KARTU VS PIN MAILER -->
        <h6 class="fw-bold text-uppercase mb-2 text-dark border-bottom pb-1">
            2. Komparasi Jumlah Fisik Kartu ATM vs Jumlah PIN Mailer
        </h6>
        <div class="table-responsive mb-3">
            <table class="table table-bordered border-dark align-middle text-center mb-0" style="font-size: 13px;">
                <thead style="background-color: #fceae6;">
                    <tr>
                        <th class="border-dark text-start ps-3" style="width: 200px;">ITEM</th>
                        <th class="border-dark" style="width: 160px;">JUMLAH KARTU ATM</th>
                        <th class="border-dark" style="width: 160px;">JUMLAH PIN MAILER</th>
                        <th class="border-dark" style="width: 140px;">SELISIH</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kartuData as $row)
                    @php
                        $selisihPin = (int)$row->jumlah_fisik_kartu - (int)$row->jumlah_pin_mailer;
                    @endphp
                    <tr>
                        <td class="text-start ps-3 fw-bold border-dark">KARTU {{ strtoupper($row->jenis_kartu) }}</td>
                        <td class="border-dark">{{ number_format($row->jumlah_fisik_kartu, 0, ',', '.') }}</td>
                        <td class="border-dark">{{ number_format($row->jumlah_pin_mailer, 0, ',', '.') }}</td>
                        <td class="border-dark fw-bold {{ $selisihPin != 0 ? 'text-danger' : 'text-success' }}">
                            {{ $selisihPin }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="fw-bold border-dark" style="background-color: #f1f2f6;">
                        <td class="text-start ps-3 text-uppercase border-dark">TOTAL</td>
                        <td class="border-dark">{{ number_format($totalFisikQty, 0, ',', '.') }}</td>
                        <td class="border-dark">{{ number_format($totalPinMailer, 0, ',', '.') }}</td>
                        <td class="border-dark {{ $selisihTotalKartuPin != 0 ? 'text-danger' : 'text-success' }}">
                            {{ $selisihTotalKartuPin }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Findings Tabel 2 -->
        <div class="p-3 mb-5 rounded bg-light border border-secondary border-opacity-25" style="font-size: 13.5px;">
            <p class="fw-bold mb-1 text-danger">Findings / Temuan PIN Mailer:</p>
            <p class="mb-0 text-justify lh-base">
                {{ $findings2 ?: ("Pin Mailer KC {$assignment->nama_unit} untuk Kartu Silver diketahui kelebihan 2 dimana salah satunya dikarenakan kesalahan petugas dalam memberikan PIN Mailer pada saat aktivasi kartu ATM; untuk Kartu Platinum berdasarkan hasil wawancara kepada petugas, dari awal adanya Kartu Platinum tidak disertai dengan PIN Mailer.") }}
            </p>
        </div>

        <!-- Blok Tanda Tangan -->
        <div class="row text-center mt-4" style="font-size: 13.5px;">
            <div class="col-4">
                <p class="fw-bold mb-5">Yang Memeriksa (RA)</p>
                <div style="height: 50px;"></div>
                <p class="fw-bold mb-0 text-decoration-underline">{{ $assignment->pemeriksa_1 ?? $assignment->resident_auditor ?? 'Resident Auditor' }}</p>
                <small class="text-muted">Resident Auditor Bank Sulteng</small>
            </div>
            <div class="col-4">
                <p class="fw-bold mb-5">Yang Menyaksikan (Cabang)</p>
                <div style="height: 50px;"></div>
                <p class="fw-bold mb-0 text-decoration-underline">{{ $assignment->saksi_1 ?? 'Petugas ATM / Customer Service' }}</p>
                <small class="text-muted">Pemimpin Seksi / Petugas Terkait</small>
            </div>
            <div class="col-4">
                <p class="fw-bold mb-5">Mengetahui</p>
                <div style="height: 50px;"></div>
                <p class="fw-bold mb-0 text-decoration-underline">{{ $assignment->pejabat_mengetahui ?? 'ADE IRMA S. MAILILI' }}</p>
                <small class="text-muted">Pemimpin Cabang / Pemimpin Cabang Pembantu</small>
            </div>
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
    table {
        border-collapse: collapse !important;
    }
    th, td {
        border-color: #000 !important;
    }
}
</style>
@endsection

