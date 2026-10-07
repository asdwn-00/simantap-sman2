<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMANTAP - Transparansi Laporan</title>
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
                Pantau perkembangan penanganan kerusakan sarana dan prasarana di lingkungan sekolah.
            </p>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">

        <div class="flex items-center mb-8">
            <div class="w-1.5 h-8 bg-[#F5C518] rounded-full mr-3"></div>
            <h3 class="text-2xl font-bold text-[#2B4885]">Daftar Laporan Terkini</h3>
        </div>

        @php
            $warnaBadge = config('simantap.warna_laporan');
            $warnaDot = ['tinggi' => 'bg-red-500', 'rendah' => 'bg-blue-400'];
            $labelPrioritas = ['tinggi' => 'Prioritas Tinggi', 'rendah' => 'Prioritas Rendah'];
        @endphp

        @if ($laporan->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center text-gray-400 border border-gray-100">
                Belum ada laporan yang tercatat.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($laporan as $item)
                    <a href="{{ route('transparansi.show', $item->laporan_id) }}"
                       class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 flex flex-col h-full">
                        <div class="flex justify-between items-center mb-4">
                            <span class="{{ $warnaBadge[$item->status_laporan] ?? '' }} text-xs font-bold px-3 py-1.5 rounded-full">{{ $item->label_status }}</span>
                            <div class="flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full {{ $warnaDot[$item->prioritas] ?? 'bg-gray-300' }}"></span>
                                <span class="text-xs font-medium text-gray-500">{{ $labelPrioritas[$item->prioritas] ?? 'Belum ditetapkan' }}</span>
                            </div>
                        </div>
                        <h4 class="text-xl font-bold text-[#2B4885] mb-2 leading-tight">{{ $item->nama_barang }}</h4>
                        <div class="flex items-start text-gray-500 mb-6 flex-grow">
                            <svg class="w-4 h-4 mr-1.5 mt-0.5 flex-shrink-0 text-[#F5C518]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ $item->nama_ruangan }}</span>
                        </div>
                        <div class="bg-[#f8fbff] rounded-2xl p-4 border border-blue-50">
                            <p class="text-xs font-bold text-[#2B4885] mb-1">{{ $item->judul_ringkasan }}:</p>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($item->teks_ringkasan, 110) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">{{ $laporan->links() }}</div>
        @endif
    </main>

</body>
</html>
