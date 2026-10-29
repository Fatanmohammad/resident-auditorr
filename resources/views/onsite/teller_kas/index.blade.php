@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="tellerKasApp()">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">KKA Teller & Kas — Onsite Audit</h1>
            <p class="text-sm text-gray-600">Unit: {{ $visit->nama_unit ?? '-' }} ({{ $visit->kode_unit ?? '-' }}) &bull; Periode: {{ $visit->periode ?? '-' }}</p>
        </div>
        <a href="{{ route('onsite.kka', $visit->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            &larr; Kembali ke Ringkasan KKA
        </a>
    </div>

    {{-- SUB-NAVIGASI 6 AREA CBS BERJEJER --}}
    <div class="flex space-x-2 border-b border-gray-200 mb-6 pb-2 overflow-x-auto">
        <a href="{{ route('onsite.kka.teller_kas.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 text-white whitespace-nowrap">1. Teller & Kas</a>
        <a href="{{ route('onsite.kka.dpk.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">2. DPK & Layanan</a>
        <a href="{{ route('onsite.kka.kredit.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">3. Kredit</a>
        <a href="{{ route('onsite.kka.biaya.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">4. Biaya & Beban</a>
        <a href="{{ route('onsite.kka.atm.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">5. Kartu ATM</a>
        <a href="{{ route('onsite.kka.pengaduan.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">6. Pengaduan</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700 font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Sample ID</th>
                        <th class="px-4 py-3 text-left">Tgl / Arsip</th>
                        <th class="px-4 py-3 text-left">Uraian / Keterangan</th>
                        <th class="px-4 py-3 text-right">Nominal (Rp)</th>
                        <th class="px-4 py-3 text-center">Jenis Sampling</th>
                        <th class="px-4 py-3 text-center">Status Uji</th>
                        <th class="px-4 py-3 text-center">Risiko</th>
                        <th class="px-4 py-3 text-center">Review</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @if(isset($findings) && count($findings) > 0)
                        @foreach($findings as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $item->sample_id ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div>{{ $item->tgl_tx ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Arsip: {{ $item->no_arsip ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $item->ket_tx ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">User: {{ $item->kd_user ?? '-' }} &bull; Alasan: {{ $item->alasan_sampling ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                    {{ isset($item->jumlah_tx) ? number_format($item->jumlah_tx, 0, ',', '.') : '0' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold
                                        @if(($item->jenis_sampling ?? '') == 'Mandatory') bg-red-100 text-red-800
                                        @elseif(($item->jenis_sampling ?? '') == 'Certainty') bg-orange-100 text-orange-800
                                        @elseif(($item->jenis_sampling ?? '') == 'Targeted') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $item->jenis_sampling ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium
                                        {{ ($item->status_uji ?? '') == 'Sesuai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->status_uji ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-bold rounded
                                        @if(($item->risk_level ?? '') == 'Kritis') bg-red-600 text-white
                                        @elseif(($item->risk_level ?? '') == 'High') bg-red-100 text-red-700
                                        @elseif(($item->risk_level ?? '') == 'Moderate') bg-yellow-100 text-yellow-700
                                        @else bg-green-100 text-green-700 @endif">
                                        {{ $item->risk_level ?? 'Low' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-semibold
                                        @if(($item->status_review ?? '') == 'Approved') text-green-600
                                        @elseif(($item->status_review ?? '') == 'Revisi') text-red-600
                                        @else text-gray-500 @endif">
                                        {{ $item->status_review ?? 'Belum' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button @click="editItem({{ json_encode($item) }})" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                                        Uji Atribut
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                Belum ada sampel transaksi Teller & Kas. Silakan lakukan upload file CBS terlebih dahulu.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            @if(isset($findings) && method_exists($findings, 'links'))
                {{ $findings->links() }}
            @endif
        </div>
    </div>

    <!-- MODAL FORM PENGUJIAN 10 ATRIBUT -->
    <div x-show="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto" style="display: none;">
        <div class="bg-white w-11/12 max-w-4xl rounded-lg shadow-xl overflow-hidden my-6">
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="font-bold text-lg">Lembar Kerja Pengujian 10 Atribut - <span x-text="form.sample_id"></span></h3>
                <button @click="isOpen = false" class="text-white hover:text-gray-200 font-bold">&times;</button>
            </div>
            
            <form @submit.prevent="submitForm" class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                <div>
                    <h4 class="font-semibold text-gray-700 mb-3 border-b pb-1">Pengujian 10 Atribut Standar SOP</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">1. Bukti Asli / Valid</span>
                            <select x-model="form.attr_bukti_asli" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">2. Kesesuaian CBS vs Dokumen</span>
                            <select x-model="form.attr_sesuai_cbs" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">3. Otorisasi Sesuai Limit</span>
                            <select x-model="form.attr_otorisasi" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">4. Identitas & Spesimen Valid</span>
                            <select x-model="form.attr_identitas" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">5. Slip & Denominasi Kas Sesuai</span>
                            <select x-model="form.attr_slip_denominasi" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">6. Tanggal/Jam & Cut-off Wajar</span>
                            <select x-model="form.attr_cutoff" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">7. Override / Reversal Wajar</span>
                            <select x-model="form.attr_override_reversal" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">8. Kepatuhan Tunai Besar</span>
                            <select x-model="form.attr_tunai_besar" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">9. Posting GL Debit / Kredit Sesuai</span>
                            <select x-model="form.attr_posting_gl" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">10. Kelengkapan Dokumentasi Pendukung</span>
                            <select x-model="form.attr_dokumentasi" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Status Uji</label>
                        <select x-model="form.status_uji" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                            <option value="Sesuai">Sesuai</option>
                            <option value="Ada Exception">Ada Exception</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Skor Dampak (1-5)</label>
                        <input type="number" x-model.number="form.skor_dampak" min="1" max="5" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Skor Kemungkinan (1-5)</label>
                        <input type="number" x-model.number="form.skor_kemungkinan" min="1" max="5" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Jenis Exception</label>
                        <input type="text" x-model="form.jenis_exception" class="w-full mt-1 border-gray-300 rounded text-sm p-2" placeholder="Contoh: Selisih Kas / Otorisasi Terlampaui">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Critical Trigger (Kritis)</label>
                        <select x-model="form.critical_trigger" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                            <option value="Tidak">Tidak</option>
                            <option value="Ya">Ya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700">Kondisi / Temuan Singkat</label>
                    <textarea x-model="form.kondisi" rows="2" class="w-full mt-1 border-gray-300 rounded text-sm p-2" placeholder="Uraikan kondisi penyimpangan..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700">Simpulan Auditor (RA)</label>
                    <textarea x-model="form.simpulan_ra" rows="2" class="w-full mt-1 border-gray-300 rounded text-sm p-2" placeholder="Kesimpulan akhir pengujian sampel..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" @click="isOpen = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded text-sm">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium">Simpan Pengujian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function tellerKasApp() {
    return {
        isOpen: false,
        form: {
            id: null,
            sample_id: '',
            attr_bukti_asli: 'Sesuai',
            attr_sesuai_cbs: 'Sesuai',
            attr_otorisasi: 'Sesuai',
            attr_identitas: 'Sesuai',
            attr_slip_denominasi: 'Sesuai',
            attr_cutoff: 'Sesuai',
            attr_override_reversal: 'Sesuai',
            attr_tunai_besar: 'Sesuai',
            attr_posting_gl: 'Sesuai',
            attr_dokumentasi: 'Sesuai',
            status_uji: 'Sesuai',
            jenis_exception: '',
            kondisi: '',
            kriteria: '',
            sebab: '',
            dampak: '',
            skor_dampak: 1,
            skor_kemungkinan: 1,
            critical_trigger: 'Tidak',
            simpulan_ra: ''
        },
        editItem(item) {
            this.form = { ...item };
            this.isOpen = true;
        },
        submitForm() {
            fetch(`/onsite/kka/teller-kas/${this.form.id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal memperbarui KKA.');
                }
            })
            .catch(err => console.error(err));
        }
    }
}
</script>
@endsection