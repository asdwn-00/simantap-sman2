<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $l->nama_barang }} - SIMANTAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-simantap.css') }}">
</head>
<body class="min-h-screen flex flex-col">

    @auth('pengguna')
        @include('partials.navbar-internal')
    @else
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-3xl font-extrabold tracking-tight text-[#2B4885]">
                            SIMAN<span class="text-[#F5C518]">TAP.</span>
                        </h1>
                    </div>

                    <div class="flex items-center space-x-8">
                        <a
                            href="{{ route('transparansi.index') }}"
                            class="text-[#2B4885] font-bold border-b-2 border-[#F5C518] pb-1"
                        >
                            Transparansi
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] font-bold py-2.5 px-6 rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                        >
                            Login Sistem
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <header class="relative py-24 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/smada-pic.png') }}" alt="Gedung Sekolah" class="w-full h-full object-cover" onerror="this.style.display='none'">
        </div>
        <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#2B4885]/85 via-[#2B4885]/65 to-[#F5C518]/50 backdrop-blur-[1px]"></div>
        <div class="absolute top-10 left-10 w-32 h-32 border-4 border-[#F5C518]/50 rounded-full z-0"></div>
        <div class="absolute top-16 right-40 w-20 h-20 border-[6px] border-[#F5C518]/40 rounded-full z-0"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-md">
                Transparansi <span class="text-[#F5C518]">Penanganan Fasilitas</span>
            </h2>
            <p class="text-lg text-white/95 font-medium max-w-2xl mx-auto drop-shadow-sm">
                Pantau perkembangan laporan perbaikan sarana dan prasarana di lingkungan sekolah secara real-time.
            </p>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">

        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <div class="w-1.5 h-8 bg-[#F5C518] rounded-full mr-3"></div>
                <h3 class="text-2xl font-bold text-[#2B4885]">Detail Laporan</h3>
            </div>
            <a href="{{ route('transparansi.index') }}" class="flex items-center text-gray-500 hover:text-[#2B4885] font-medium transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar
            </a>
        </div>

        @php
            $warnaBadge = config('simantap.warna_laporan');
            $warnaDot = ['tinggi' => 'bg-red-500', 'sedang' => 'bg-[#F5C518]', 'rendah' => 'bg-blue-400'];
            $warnaTeks = ['tinggi' => 'text-red-600 bg-red-50', 'sedang' => 'text-amber-600 bg-amber-50', 'rendah' => 'text-blue-600 bg-blue-50'];
            $labelPrioritas = ['tinggi' => 'Prioritas Tinggi', 'sedang' => 'Prioritas Menengah', 'rendah' => 'Prioritas Rendah'];
            $tahapan = [
                ['Masuk', 1], ['Diperiksa', 2], ['Disetujui', 3], ['Ditangani', 4], ['Selesai', 5],
            ];
        @endphp

        <div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-[100px] -z-0 opacity-50"></div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <div class="flex items-center space-x-3 mb-3">
                            <span class="{{ $warnaBadge[$l->status_laporan] ?? '' }} text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">{{ $label }}</span>
                            @if ($l->prioritas)
                                <span class="flex items-center text-xs font-semibold {{ $warnaTeks[$l->prioritas] }} px-3 py-1.5 rounded-full">
                                    <span class="w-2 h-2 rounded-full {{ $warnaDot[$l->prioritas] }} mr-1.5"></span> {{ $labelPrioritas[$l->prioritas] }}
                                </span>
                            @endif
                        </div>
                        <h4 class="text-3xl font-extrabold text-[#2B4885] leading-tight mb-2">{{ $l->nama_barang }}</h4>
                        <div class="flex flex-wrap items-center text-gray-500 text-sm gap-4">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ $l->nama_ruangan }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Dilaporkan: {{ \Carbon\Carbon::parse($l->tanggal_laporan)->translatedFormat('d M Y') }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                ID: LAP-{{ str_pad($l->laporan_id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>

                    @if ($l->status_laporan !== 'dihentikan')
                    <div class="flex-shrink-0 flex items-center justify-center w-20 h-20 rounded-full border-4 border-blue-100 relative">
                        <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-blue-500" stroke-dasharray="{{ $persenSelesai }}, 100" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="text-xl font-bold text-[#2B4885]">{{ $persenSelesai }}%</span>
                    </div>
                    @endif
                </div>

                <hr class="border-gray-100 mb-8">

                @if ($l->status_laporan === 'dihentikan')
                    <div class="bg-red-50 text-red-800 rounded-xl p-4 mb-10">
                        <h5 class="font-bold">Laporan Dihentikan</h5>
                        <p class="mt-2">{{ $l->alasan_penghentian }}</p>
                        <p class="text-sm mt-2">Tanggal penghentian: {{ \Carbon\Carbon::parse($l->tanggal_ditutup)->translatedFormat('d M Y H:i') }}</p>
                    </div>
                @else
                <div class="mb-10">
                    <h5 class="text-lg font-bold text-[#2B4885] mb-6">Status Pengerjaan</h5>
                    <div class="relative flex justify-between items-center w-full">
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 rounded-full z-0"></div>
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-blue-500 rounded-full z-0 transition-all duration-500"
                             style="width: {{ max(0, ($doneNodes - 1)) / 4 * 100 }}%"></div>

                        @foreach ($tahapan as [$namaTahap, $urutan])
                            @php
                                $selesai = $urutan < $doneNodes || $persenSelesai === 100;
                                $sedang = $urutan === $doneNodes && $persenSelesai < 100;
                            @endphp
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-md ring-4 ring-white
                                    {{ $selesai ? 'bg-blue-500' : ($sedang ? 'bg-[#F5C518] animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-xs font-semibold mt-2 {{ $selesai ? 'text-[#2B4885]' : ($sedang ? 'font-bold text-[#F5C518]' : 'text-gray-400 font-medium') }}">{{ $namaTahap }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <h5 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Rincian Keluhan</h5>
                        <ul class="space-y-4">
                            <li>
                                <p class="text-xs text-gray-500 font-medium mb-1">Kondisi Saat Dilaporkan</p>
                                <p class="text-sm text-gray-800">{{ $l->kerusakan }}</p>
                            </li>
                            @if ($pemeriksaanTerbaru?->temuan)
                                <li>
                                    <p class="text-xs text-gray-500 font-medium mb-1">Hasil Pemeriksaan Petugas</p>
                                    <p class="text-sm text-gray-800">{{ $pemeriksaanTerbaru->temuan }}</p>
                                </li>
                            @endif
                            @if ($pemeriksaanTerbaru?->rekomendasi)
                                <li>
                                    <p class="text-xs text-gray-500 font-medium mb-1">Rekomendasi Tindakan</p>
                                    <p class="text-sm text-gray-800">
                                        {{ $pemeriksaanTerbaru->label_rekomendasi }}.
                                        @if ($pemeriksaanTerbaru->sumber_pengganti)
                                            Sumber: {{ $pemeriksaanTerbaru->sumber_pengganti === 'stok_gudang' ? 'stok gudang.' : 'pengadaan baru.' }}
                                        @endif
                                    </p>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="bg-[#f8fbff] rounded-2xl p-6 border border-blue-100">
                        <h5 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">Pembaruan Terkini</h5>
                        <div class="relative pl-6 border-l-2 border-blue-200 space-y-6">
                            @foreach ($pembaruan as $i => $update)
                                <div class="relative">
                                    <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full {{ $i === 0 ? 'bg-[#F5C518]' : 'bg-gray-300' }} ring-4 ring-[#f8fbff]"></div>
                                    <p class="text-xs text-gray-500 font-medium mb-1">{{ \Carbon\Carbon::parse($update['tanggal'])->translatedFormat('d M Y, H:i') }} WIB</p>
                                    <p class="text-sm {{ $i === 0 ? 'text-[#2B4885] font-semibold' : 'text-gray-700 font-semibold' }} mb-1">{{ $update['judul'] }}</p>
                                    <p class="text-sm text-gray-600 leading-relaxed">{{ $update['teks'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
