@extends('layouts.app') <!-- Sesuaikan dengan nama file master layout/template kamu -->

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Upload Data DUMP Offsite</h2>
        <p class="text-gray-600">Unggah file CSV Core Banking System untuk dianalisis oleh mesin Offsite Audit.</p>
    </div>

    <!-- Tampilkan Pesan Sukses atau Error -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Upload -->
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form action="{{ route('offsite.upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_file">
                    Pilih Jenis File DUMP
                </label>
                <select name="jenis_file" id="jenis_file" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">-- Pilih Jenis File --</option>
                    <option value="DUMP_01">DUMP 01 - Transaksi Teller / CBS</option>
                    <option value="DUMP_02">DUMP 02 - DPK / CS SPU</option>
                    <option value="DUMP_03">DUMP 03 - Kredit</option>
                    <option value="DUMP_04">DUMP 04 - Beban Biaya</option>
                    <option value="DUMP_05">DUMP 05 - Pengaduan</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="file_csv">
                    File CSV DUMP
                </label>
                <input type="file" name="file_csv" id="file_csv" accept=".csv" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-sm text-gray-500 mt-1">Pastikan format file adalah .csv dengan ukuran maksimal 50MB.</p>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                    Proses dan Analisis Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection