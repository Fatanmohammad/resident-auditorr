@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="kreditApp()">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">KKA Kredit — Onsite Audit</h1>
            <p class="text-sm text-gray-600">Unit: {{ $visit->nama_unit }} ({{ $visit->kode_unit }}) \vert{} Periode: {{$visit->periode }}</p>
        </div>
        <a href="{{ route('onsite.kka', $visit->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            &larr; Kembali ke Ringkasan KKA
        </a>
    </div>

    <!-- Sub Navigasi 6 Area CBS -->
    <div class="flex space-x-2 border-b border-gray-200 mb-6 pb-2 overflow-x-auto">
        <a href="{{ route('kka.teller_kas.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">1. Teller & Kas</a>
        <a href="{{ route('kka.dpk.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">2. DPK & Layanan</a>
        <a href="{{ route('kka.kredit.index', $visit->id) }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 text-white whitespace-nowrap">3. Kredit</a>
        <a href="#" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed whitespace-nowrap">4. Biaya & Beban</a>
        <a href="#" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed whitespace-nowrap">5. Kartu ATM</a>
        <a href="#" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed whitespace-nowrap">6. Pengaduan</a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700 font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Sample ID</th>
                        <th class="px-4 py-3 text-left">No. Rekening / Debitur</th>
                        <th class="px-4 py-3 text-left">Tgl Akad</th>
                        <th class="px-4 py-3 text-right">Plafon (Rp)</th>
                        <th class="px-4 py-3 text-center">Jenis Sampling</th>
                        <th class="px-4 py-3 text-center">Status Uji</th>
                        <th class="px-4 py-3 text-center">Risiko</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($findings as$item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $item->sample_id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $item->no_rekening }}</div>
                                <div class="text-xs text-gray-500">{{ $item->nama_debitur }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $item->tgl_akad }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                {{ number_format($item->plafon, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold
                                    @if($item->jenis_sampling == 'Mandatory') bg-red-100 text-red-800
                                    @elseif($item->jenis_sampling == 'Certainty') bg-orange-100 text-orange-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $item->jenis_sampling }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    {{ $item->status_uji == 'Sesuai' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $item->status_uji }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-bold rounded
                                    @if($item->risk_level == 'Kritis') bg-red-600 text-white
                                    @elseif($item->risk_level == 'High') bg-red-100 text-red-700
                                    @elseif($item->risk_level == 'Moderate') bg-yellow-100 text-yellow-700
                                    @else bg-green-100 text-green-700 @endif">
                                    {{ $item->risk_level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <button @click="editItem({{ json_encode($item) }})" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                                    Uji Atribut Kredit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Belum ada data sampel Kredit. Silakan lakukan sinkronisasi/upload data kredit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {{ $findings->links() }}
        </div>
    </div>

    <!-- MODAL PENGUJIAN 10 ATRIBUT KREDIT -->
    <div x-show="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto" style="display: none;">
        <div class="bg-white w-11/12 max-w-4xl rounded-lg shadow-xl overflow-hidden my-6">
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="font-bold text-lg">Pengujian 10 Atribut Kredit - <span x-text="form.sample_id"></span></h3>
                <button @click="isOpen = false" class="text-white hover:text-gray-200 font-bold">&times;</button>
            </div>
            
            <form @submit.prevent="submitForm" class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                <div>
                    <h4 class="font-semibold text-gray-700 mb-3 border-b pb-1">10 Atribut Pengujian Kepatuhan Kredit</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        @foreach([
                            ['attr_analisa_kelayakan', '1. Analisa Kelayakan & Scoring Kredit'],
                            ['attr_otorisasi_kredit', '2. Otorisasi Putusan Sesuai Limit Wewenang'],
                            ['attr_keabsahan_agunan', '3. Keabsahan & Penilaian Agunan (Appraisal)'],
                            ['attr_pengikatan_hukum', '4. Pengikatan Hukum / SKMHT / APHT'],
                            ['attr_asuransi_kredit', '5. Penutupan Asuransi Jiwa & Agunan'],
                            ['attr_kepatuhan_kolektibilitas', '6. Kepatuhan Penetapan Kolektibilitas'],
                            ['attr_pemenuhan_syarat_cair', '7. Pemenuhan Syarat Pencairan (PK/Notariil)'],
                            ['attr_kesesuaian_baki_debet', '8. Kesesuaian Saldo Baki Debet CBS'],
                            ['attr_posting_gl', '9. Keakuratan Posting GL Akuntansi'],
                            ['attr_kelengkapan_berkas', '10. Kelengkapan Berkas Berkas Berkas Kredit']
                        ] as [$field,$label])
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded border">
                            <span class="text-gray-700 font-medium text-xs">{{ $label }}</span>
                            <select x-model="form.{{ $field }}" class="border-gray-300 rounded text-xs p-1">
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Skor Dampak (1-5)</label>
                        <input type="number" x-model.number="form.skor_dampak" min="1" max="5" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Skor Kemungkinan (1-5)</label>
                        <input type="number" x-model.number="form.skor_kemungkinan" min="1" max="5" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Critical Trigger (Kritis)</label>
                        <select x-model="form.critical_trigger" class="w-full mt-1 border-gray-300 rounded text-sm p-2">
                            <option value="Tidak">Tidak</option>
                            <option value="Ya">Ya</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Jenis Exception</label>
                        <input type="text" x-model="form.jenis_exception" class="w-full mt-1 border-gray-300 rounded text-sm p-2" placeholder="Contoh: Agunan Belum Diikat Sempurna">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Kondisi / Temuan</label>
                        <input type="text" x-model="form.kondisi" class="w-full mt-1 border-gray-300 rounded text-sm p-2" placeholder="Uraikan kondisi temuan kredit...">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700">Simpulan Auditor (RA)</label>
                    <textarea x-model="form.simpulan_ra" rows="2" class="w-full mt-1 border-gray-300 rounded text-sm p-2" placeholder="Kesimpulan akhir pengujian kredit..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" @click="isOpen = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded text-sm">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium">Simpan Pengujian Kredit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function kreditApp() {
    return {
        isOpen: false,
        form: {
            id: null,
            sample_id: '',
            attr_analisa_kelayakan: 'Sesuai',
            attr_otorisasi_kredit: 'Sesuai',
            attr_keabsahan_agunan: 'Sesuai',
            attr_pengikatan_hukum: 'Sesuai',
            attr_asuransi_kredit: 'Sesuai',
            attr_kepatuhan_kolektibilitas: 'Sesuai',
            attr_pemenuhan_syarat_cair: 'Sesuai',
            attr_kesesuaian_baki_debet: 'Sesuai',
            attr_posting_gl: 'Sesuai',
            attr_kelengkapan_berkas: 'Sesuai',
            jenis_exception: '',
            kondisi: '',
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
            fetch(`/onsite/kka/kredit/${this.form.id}`, {
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
                    alert('Gagal memperbarui KKA Kredit.');
                }
            })
            .catch(err => console.error(err));
        }
    }
}
</script>
@endsection