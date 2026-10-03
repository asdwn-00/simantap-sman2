<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Koordinator - SIMANTAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fb; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
</head>
<body class="min-h-screen text-gray-800">

    @include('partials.navbar-internal')

    <main class="max-w-[1400px] mx-auto px-6 py-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <div class="xl:col-span-2 space-y-8">

                <div class="bg-[#2B4885] rounded-[2rem] p-8 md:p-10 relative overflow-hidden shadow-lg">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
                    <div class="absolute bottom-0 right-32 w-32 h-32 bg-[#F5C518] opacity-20 rounded-full translate-y-1/2"></div>

                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="mb-6 md:mb-0">
                            <h2 class="text-3xl font-extrabold text-white mb-2">{{ $sapaan }}, {{ $pengguna->nama }}!</h2>
                            <p class="text-blue-100 text-sm max-w-md leading-relaxed">
                                @if ($belumAdaPemeriksa === 0 && $rekomendasiMenungguTinjauan->isEmpty() && $danaMenunggu->isEmpty())
                                    Tidak ada laporan, rekomendasi, atau pengajuan dana yang menunggu keputusanmu saat ini.
                                @else
                                    Ada {{ $belumAdaPemeriksa }} laporan yang butuh penugasan,
                                    {{ $rekomendasiMenungguTinjauan->count() }} rekomendasi menunggu tinjauan Anda,
                                    dan {{ $danaMenunggu->count() }} pengajuan dana menunggu keputusan. Mari cek perkembangannya.
                                @endif
                            </p>
                        </div>
                        <a href="#aktivitas" class="bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] font-bold py-3 px-6 rounded-xl shadow-md transition-transform transform hover:-translate-y-1 whitespace-nowrap">
                            Tinjau Pengajuan
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-3xl font-black text-[#2B4885]">{{ $belumAdaPemeriksa }}</h3>
                        <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wide">Belum Ada Pemeriksa</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <h3 class="text-3xl font-black text-[#2B4885]">{{ $rekomendasiMenungguTinjauan->count() }}</h3>
                        <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wide">Rekomendasi Menunggu Tinjauan</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-500 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-3xl font-black text-[#2B4885]">{{ $danaMenunggu->count() }}</h3>
                        <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wide">Dana Menunggu Keputusan</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-3xl font-black text-[#2B4885]">{{ $siapDitutup->count() }}</h3>
                        <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wide">Siap Ditutup</p>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Beban Tugas Petugas Sarpras</h3>
                        <a href="{{ route('penugasan.index') }}" class="text-xs font-bold text-[#2B4885] hover:underline">Buka Penugasan &rarr;</a>
                    </div>

                    <div class="space-y-3 mt-4">
                        @forelse ($bebanTugasPetugas as $p)
                            <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-11 h-11 rounded-full bg-[#2B4885] flex items-center justify-center text-white font-bold text-sm">
                                        {{ Str::of($p->nama)->substr(0, 1)->upper() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $p->nama }}</p>
                                        <p class="text-xs text-gray-400">{{ $p->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-1 text-[11px] font-bold rounded-full {{ $p->tugas_aktif > 0 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500' }}">{{ $p->tugas_aktif }} tugas aktif</span>
                                    <a href="{{ route('penugasan.index') }}" class="bg-[#2B4885] hover:bg-blue-800 text-white text-xs font-bold py-2 px-4 rounded-lg">Beri Tugas</a>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Belum ada akun dengan role Petugas Sarpras.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="space-y-8">

                <div id="aktivitas" class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 scroll-mt-24">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-bold text-[#2B4885]">Aktivitas yang Perlu Ditangani</h3>
                    </div>
                    <div class="space-y-3">
                        @if ($belumAdaPemeriksa > 0)
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-gray-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $belumAdaPemeriksa }} laporan belum ada pemeriksa</p>
                                        <p class="text-xs text-gray-400">Perlu ditugaskan ke petugas</p>
                                    </div>
                                </div>
                                <a href="{{ route('penugasan.index') }}" class="bg-[#2B4885] hover:bg-blue-800 text-white text-xs font-bold py-1.5 px-3 rounded-lg whitespace-nowrap">Tugaskan</a>
                            </div>
                        @endif

                        @if ($rekomendasiMenungguTinjauan->isNotEmpty())
                            @php $r1 = $rekomendasiMenungguTinjauan->first(); @endphp
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-gray-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $rekomendasiMenungguTinjauan->count() }} rekomendasi menunggu tinjauan</p>
                                        <p class="text-xs text-gray-400">{{ $r1->laporan->kode_laporan ?? '-' }} &middot; {{ $r1->laporan->inventaris->nama_barang ?? '-' }}, {{ $r1->laporan->ruangan->nama_ruangan ?? '-' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('laporan.show', $r1->laporan_id) }}" class="bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] text-xs font-bold py-1.5 px-3 rounded-lg whitespace-nowrap">Tinjau Laporan</a>
                            </div>
                        @endif

                        @if ($danaMenunggu->isNotEmpty())
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-gray-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 font-black text-xs">Rp</div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $danaMenunggu->count() }} pengajuan dana menunggu keputusan</p>
                                        <p class="text-xs text-gray-400">Termasuk {{ $danaMenunggu->where('status', 'revisi')->count() }} versi revisi terbaru</p>
                                    </div>
                                </div>
                                <a href="{{ route('dana.index') }}" class="bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] text-xs font-bold py-1.5 px-3 rounded-lg whitespace-nowrap">Tinjau Dana</a>
                            </div>
                        @endif

                        @if ($siapDitutup->isNotEmpty())
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-gray-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $siapDitutup->count() }} laporan siap ditutup</p>
                                        <p class="text-xs text-gray-400">Konfirmasi pelapor sudah "Sesuai"</p>
                                    </div>
                                </div>
                                <a href="{{ route('laporan.show', $siapDitutup->first()->laporan_id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-1.5 px-3 rounded-lg whitespace-nowrap">Tutup Laporan</a>
                            </div>
                        @endif

                        @if ($belumAdaPemeriksa === 0 && $rekomendasiMenungguTinjauan->isEmpty() && $danaMenunggu->isEmpty() && $siapDitutup->isEmpty())
                            <p class="text-sm text-gray-400 text-center py-4">Tidak ada aktivitas yang perlu ditangani saat ini.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-[#2B4885]">Pengajuan Dana Menunggu Tinjauan</h3>
                        <a href="{{ route('dana.index') }}" class="text-xs font-semibold text-blue-500 hover:text-blue-700">Semua Pengajuan</a>
                    </div>

                    <div class="space-y-4">
                        @forelse ($danaMenunggu as $dana)
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                                <div class="flex items-center space-x-3">
                                    @if ($dana->status_pengajuan === 'revisi')
                                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 font-black text-sm flex-shrink-0">!</div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 font-black text-xs flex-shrink-0">Rp</div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">
                                            @if ($dana->status_pengajuan === 'revisi')Revisi: @endif{{ \Illuminate\Support\Str::limit($dana->rincian_kebutuhan, 28) }}
                                        </p>
                                        <p class="text-xs text-gray-400">Oleh: {{ $dana->pembuat->nama ?? '-' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('dana.index') }}"
                                   class="inline-block text-xs font-bold py-1.5 px-3 rounded-lg
                                   {{ $dana->status_pengajuan === 'revisi' ? 'bg-gray-200 hover:bg-gray-300 text-gray-700' : 'bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885]' }}">
                                    {{ $dana->status_pengajuan === 'revisi' ? 'Cek' : 'Tinjau' }}
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Tidak ada pengajuan dana yang menunggu.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>
