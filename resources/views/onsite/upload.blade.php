@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Upload Data CBS Onsite</h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
        Kunjungan: <strong>{{ $visit->kode_unit }} — {{ $visit->nama_unit }}</strong> &nbsp;|&nbsp;
        {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
    </p>
</div>

<div class="card" style="max-width: 600px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
    <div class="card-body" style="padding: 2rem;">

        <form action="{{ route('onsite.upload.process', $visit->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    Tujuan Pengiriman Data
                </label>
                <div style="padding: 0.6rem 0.8rem; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #1e3a8a; font-weight: 600;">
                    <i class="bi bi-send-check-fill me-2"></i> Admin Pusat (KCU Palu)
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    File CBS Populasi Teller <span style="color: #dc2626;">*</span>
                </label>
                <input type="file" name="file_cbs" accept=".csv,.txt" required
                    class="form-control" style="font-size: 0.9rem; border-radius: 6px;">
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem; margin-bottom: 0;">
                    Format tab-delimited (.csv / .txt), header pertama harus <strong>KD_TX</strong>. Maksimal 20MB.
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 600; padding: 0.6rem; border-radius: 6px; background-color: #1e3a8a; border-color: #1e3a8a;">
                <i class="bi bi-cloud-upload me-1"></i> Proses dan Kirim Data ke Pusat
            </button>
        </form>

    </div>
</div>

@endsection
