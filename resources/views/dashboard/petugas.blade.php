<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas Sarpras - SIMANTAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fb; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-simantap.css') }}">
</head>
<body class="min-h-screen text-gray-800">

    @include('partials.navbar-internal')

    <main class="max-w-[1400px] mx-auto px-6 py-8">
        @if (session('sukses'))
            <div class="bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-xl px-4 py-3 mb-6">{{ session('sukses') }}</div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <div class="xl:col-span-2 space-y-8">

                <div class="bg-[#2B4885] rounded-[2rem] p-8 md:p-10 relative overflow-hidden shadow-lg">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="mb-6 md:mb-0">
                            <h2 class="text-3xl font-extrabold text-white mb-2">Halo, {{ $pengguna->nama }}!</h2>
                            <p class="text-blue-100 text-sm max-w-md leading-relaxed">
                                @if ($tugasAktif === 0 && $danaSaya->isEmpty())
                                    Tidak ada tugas aktif atau pengajuan dana yang perlu dipantau saat ini.
                                @else
                                    Anda memiliki {{ $tugasAktif }} tugas aktif dan {{ $danaSaya->count() }} pengajuan dana yang perlu dipantau statusnya hari ini.
                                @endif
                            </p>
                        </div>
                        <a href="#tugas-saya" class="bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] font-bold py-3 px-6 rounded-xl shadow-md transition-transform transform hover:-translate-y-1 whitespace-nowrap">
                            Lihat Tugas Saya
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 mb-3 font-bold text-lg">{{ $tugasAktif }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Tugas Aktif</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Dalam Pengerjaan</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center text-yellow-500 mb-3 font-bold text-lg">{{ $danaDiajukan }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Dana Diajukan</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Menunggu Acc</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-500 mb-3 font-bold text-lg">{{ $danaRevisi }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Revisi Dana</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Perlu Perbaikan</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-500 mb-3 font-bold text-lg">{{ $perluDikonfirmasi->count() }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Perlu Dikonfirmasi</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Hasil Laporan Sendiri</p>
                    </div>
                </div>

                <div id="tugas-saya" class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 scroll-mt-24">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Daftar Tugas Saya</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase">
                                    <th class="py-4">Lokasi & Kasus</th>
                                    <th class="py-4">Jenis Tugas</th>
                                    <th class="py-4">Status</th>
                                    <th class="py-4">Aksi Lapangan</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-50">
                                @forelse ($daftarTugas as $t)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 font-bold text-gray-800">{{ $t->lokasi }}<br><span class="text-xs font-normal text-gray-400">{{ $t->kasus }}</span></td>
                                        <td class="py-4 text-gray-600">{{ $t->jenis }}</td>
                                        <td class="py-4"><span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full {{ $t->warna }}">{{ $t->label }}</span></td>
                                        <td class="py-4">
                                            <a href="{{ route('laporan.show', $t->laporan_id) }}"
                                               class="inline-block text-xs font-bold py-2 px-4 rounded-lg
                                               {{ $t->aksi === 'Detail Tugas' ? 'bg-gray-200 text-gray-700' : 'bg-[#2B4885] hover:bg-blue-800 text-white' }}">
                                                {{ $t->aksi }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-sm text-gray-400">Tidak ada tugas untuk Anda saat ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-8">

                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-[#2B4885] mb-4">Pengajuan Dana Saya</h3>
                    <div class="space-y-3">
                        @forelse ($danaSaya as $dana)
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-xs text-gray-800">{{ \Illuminate\Support\Str::limit($dana->rincian_kebutuhan, 28) }}</span>
                                    @if ($dana->status_pengajuan === 'revisi')
                                        <span class="text-[10px] bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded">Revisi</span>
                                    @else
                                        <span class="text-[10px] bg-yellow-100 text-yellow-700 font-bold px-2 py-0.5 rounded">Pending</span>
                                    @endif
                                </div>
                                @if ($dana->status_pengajuan === 'revisi' && $dana->catatan)
                                    <p class="text-[11px] text-gray-400">Catatan Koordinator: {{ $dana->catatan }}</p>
                                @else
                                    <p class="text-[11px] text-gray-400">Estimasi: Rp {{ number_format($dana->estimasi_biaya, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">Tidak ada pengajuan dana yang sedang berjalan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-[#2B4885] mb-1">Menunggu Konfirmasi Saya</h3>

                    <div class="space-y-3 mt-3">
                        @forelse ($perluDikonfirmasi as $tugas)
                            @php
                                $laporan = $tugas->pemeriksaan->laporan;
                                $formId = 'form-konfirmasi-'.$tugas->penugasan_id;
                            @endphp
                            <div class="border border-gray-100 rounded-xl p-4">
                                <p class="text-xs font-bold text-gray-800">{{ $laporan->kode_laporan }} &middot; {{ $laporan->inventaris->nama_barang ?? '-' }}</p>
                                <p class="text-[11px] text-gray-400 mb-2">{{ $laporan->ruangan->nama_ruangan ?? '-' }} &middot; Dikerjakan oleh: {{ $tugas->petugas->nama ?? '-' }}</p>
                                @if ($tugas->hasil)
                                    <p class="text-[11px] text-gray-500 bg-gray-50 rounded-lg p-2 mb-3">Hasil: "{{ $tugas->hasil }}"</p>
                                @endif

                                <button type="button" onclick="document.getElementById('{{ $formId }}').classList.toggle('hidden')"
                                        class="w-full bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] text-xs font-bold py-2 px-3 rounded-lg">
                                    Konfirmasi Hasil
                                </button>

                                <form id="{{ $formId }}" action="{{ route('konfirmasi.store', $tugas->penugasan_id) }}" method="POST" class="hidden mt-3 pt-3 border-t border-gray-100 space-y-2">
                                    @csrf
                                    <div class="flex gap-2">
                                        <label class="flex-1 flex items-center justify-center gap-1 text-[11px] font-bold border border-gray-200 rounded-lg py-1.5 cursor-pointer has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-400 has-[:checked]:text-emerald-700">
                                            <input type="radio" name="hasil_konfirmasi" value="sesuai" class="accent-emerald-600" required> Sesuai
                                        </label>
                                        <label class="flex-1 flex items-center justify-center gap-1 text-[11px] font-bold border border-gray-200 rounded-lg py-1.5 cursor-pointer has-[:checked]:bg-red-50 has-[:checked]:border-red-400 has-[:checked]:text-red-700">
                                            <input type="radio" name="hasil_konfirmasi" value="masih_bermasalah" class="accent-red-600" required> Masih Bermasalah
                                        </label>
                                    </div>
                                    <textarea name="catatan" placeholder="Catatan (wajib jika masih bermasalah)" rows="2"
                                              class="w-full text-[11px] border border-gray-200 rounded-lg p-2 resize-none"></textarea>
                                    <button type="submit" class="w-full bg-[#2B4885] hover:bg-blue-800 text-white text-xs font-bold py-2 rounded-lg">
                                        Kirim Konfirmasi
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">Tidak ada laporan Anda yang menunggu konfirmasi saat ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
