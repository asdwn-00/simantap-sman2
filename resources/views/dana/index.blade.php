<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Dana - SIMANTAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fb; overflow-x: hidden; } 
        .fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .modal-enter { opacity: 0; transform: scale(0.95); }
        .modal-enter-active { opacity: 1; transform: scale(1); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .modal-leave { opacity: 1; transform: scale(1); }
        .modal-leave-active { opacity: 0; transform: scale(0.95); transition: all 0.2s ease-in; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
</head>

<body class="min-h-screen text-gray-800 relative">
@php $pengguna = $akun; @endphp

    @include('partials.navbar-internal')

    <main class="max-w-[1400px] mx-auto px-6 py-8">
        @if (session('sukses'))
            <div
                role="status"
                class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl px-4 py-3 mb-6"
            >
                {{ session('sukses') }}
            </div>
        @endif
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 fade-in">
            <div>
                <h2 class="text-3xl font-extrabold text-[#2B4885] mb-2">Manajemen Pengajuan Dana</h2>
                <p id="role-description" class="text-sm text-gray-500">{{ $akun->isKoordinator() ? 'Pantau pengajuan dana perbaikan sarpras dan status keputusannya.' : ($akun->isPetugas() ? 'Pantau pengajuan dana yang Anda buat.' : 'Pantau pengadaan dan pengajuan dana terkait laboratorium Anda.') }}</p>
            </div>
            
            @if ($akun->isPetugas())
            <button
                id="btn-tambah-dana"
                type="button"
                onclick="document.getElementById('modalBuatDana').classList.remove('hidden')"
                class="flex bg-[#2B4885] hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-transform transform hover:-translate-y-1 items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Pengajuan Dana
            </button>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 fade-in" style="animation-delay: 0.1s;">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Diajukan</p>
                    <h3 class="text-2xl font-black text-[#2B4885] mt-1">{{ $ringkasan['total'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Menunggu Keputusan</p>
                    <h3 class="text-2xl font-black text-yellow-500 mt-1">{{ $ringkasan['diajukan'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pengajuan Disetujui</p>
                    <h3 class="text-2xl font-black text-green-500 mt-1">{{ $ringkasan['disetujui'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Perlu Revisi</p>
                    <h3 class="text-2xl font-black text-red-500 mt-1">{{ $ringkasan['revisi'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 fade-in" style="animation-delay: 0.2s;">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-extrabold text-[#2B4885]">Daftar Pengajuan</h3>
                <div class="flex space-x-2">
                    <form method="GET"><select name="status" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-xs rounded-lg px-3 py-2 font-medium focus:outline-none focus:ring-2 focus:ring-[#F5C518]">
<option value="">Semua Status</option>
@foreach (['diajukan','disetujui','revisi','ditolak'] as $status)
<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
@endforeach
</select></form>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-2">ID & Keperluan</th>
                            <th class="py-4 px-2">Lokasi Terkait</th>
                            <th class="py-4 px-2">Diajukan Oleh</th>
                            <th class="py-4 px-2">Estimasi Biaya</th>
                            <th class="py-4 px-2">Status</th>
                            <th class="py-4 px-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-50" id="dana-table-body">
@forelse ($dana as $item)
@php
    $lap = $item->pemeriksaan->laporan;
    $warna = ['diajukan'=>'bg-yellow-100 text-yellow-700','disetujui'=>'bg-green-100 text-green-700','revisi'=>'bg-blue-100 text-blue-700','ditolak'=>'bg-red-100 text-red-700'];
@endphp
<tr class="hover:bg-gray-50 transition-colors">
    <td class="py-4 px-2"><p class="font-bold text-[#2B4885]">DN-{{ str_pad($item->pengajuan_id, 3, '0', STR_PAD_LEFT) }}</p><p class="text-xs text-gray-800 font-medium mt-0.5">{{ $item->rincian_kebutuhan }}</p></td>
    <td class="py-4 px-2 text-gray-600 text-xs">{{ $lap->ruangan->nama_ruangan }}</td>
    <td class="py-4 px-2"><div class="flex items-center"><div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold mr-2">{{ Str::substr($item->pembuat->nama, 0, 1) }}</div><span class="text-xs font-medium text-gray-600">{{ $item->pembuat->nama }}</span></div></td>
    <td class="py-4 px-2 font-bold text-gray-800">{{ $akun->isPjLab() ? 'Tidak ditampilkan' : 'Rp '.number_format($item->estimasi_biaya, 0, ',', '.') }}</td>
    <td class="py-4 px-2"><span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full {{ $warna[$item->status_pengajuan] }}">{{ ucfirst($item->status_pengajuan) }}</span>
    @if ($item->pengajuanBerikutnya)<p class="text-[9px] text-gray-400 mt-1 italic">Sudah ada versi lanjutan</p>@endif
    @unless ($akun->isPjLab())<p class="text-[9px] text-gray-400 mt-1 italic">{{ $item->catatan }}</p>@endunless</td>
    <td class="py-4 px-2 text-center">
        <button
            type="button"
            onclick="document.getElementById('detail-{{ $item->pengajuan_id }}').classList.remove('hidden')"
            class="bg-gray-100 text-gray-600 hover:bg-gray-200 font-bold py-1.5 px-4 rounded-lg text-xs transition-colors"
        >
            Lihat Detail
        </button>
    </td>
</tr>
@empty
<tr><td colspan="6" class="py-4 px-2 text-gray-500 text-sm">Belum ada pengajuan dana sesuai cakupan atau pencarian Anda.</td></tr>
@endforelse
</tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $dana->links() }}</div>
        @unless ($akun->isPjLab())<p class="text-xs text-gray-500 mt-4">Pembuatan dan keputusan pengajuan dana sudah tersedia. Form pengiriman versi revisi oleh petugas masih dalam pengembangan.</p>@endunless
    </main>


@foreach ($dana as $item)
<div
    id="detail-{{ $item->pengajuan_id }}"
    class="fixed inset-0 z-50 {{ $errors->getBag('keputusanDana'.$item->pengajuan_id)->any() ? '' : 'hidden' }} flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity p-6"
>
    <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl relative max-h-[85vh] overflow-y-auto">
        <button type="button" onclick="document.getElementById('detail-{{ $item->pengajuan_id }}').classList.add('hidden')" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition-colors">&times;</button>
        <h3 class="text-xl font-extrabold text-[#2B4885] mb-4">Detail Pengajuan #{{ $item->pengajuan_id }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ $item->rincian_kebutuhan }}</p>
        <p class="text-sm text-gray-600">{{ $item->pemeriksaan->laporan->ruangan->nama_ruangan }}</p>
        <p class="text-sm text-gray-600">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }} | {{ ucfirst($item->status_pengajuan) }}</p>
        @unless ($akun->isPjLab())
        <p class="text-sm text-gray-600 mt-4">Rp {{ number_format($item->estimasi_biaya, 0, ',', '.') }}</p>
        <p class="text-sm text-gray-600 mt-4">{{ $item->catatan ?: 'Belum ada catatan.' }}</p>
        @endunless
        @if ($bolehLihatLaporan->contains($item->pemeriksaan->laporan_id))
        <a href="{{ route('laporan.show', $item->pemeriksaan->laporan_id) }}" class="inline-block bg-[#2B4885] text-white font-bold py-2.5 px-6 rounded-xl mt-6 text-sm">Buka Laporan</a>
        @endif
        @include('dana.form-keputusan')
        @include('dana.form-revisi')
       
    </div>
</div>
@endforeach

@include('dana.form-buat')

</body>
</html>
