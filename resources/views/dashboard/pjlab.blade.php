<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PJ Lab - SIMANTAP</title>
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
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl px-4 py-3">
                {{ session('sukses') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-xl px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <div class="xl:col-span-2 space-y-8">

                <div class="bg-[#2B4885] rounded-[2rem] p-8 md:p-10 relative overflow-hidden shadow-lg">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="mb-6 md:mb-0">
                            <h2 class="text-3xl font-extrabold text-white mb-2">{{ $sapaan }}, {{ $pengguna->nama }}!</h2>
                            <p class="text-blue-100 text-sm max-w-md leading-relaxed">
                                @if ($perluDikonfirmasi->count() > 0)
                                    Ada {{ $perluDikonfirmasi->count() }} hasil pekerjaan yang menunggu konfirmasi Anda hari ini.
                                @else
                                    Tidak ada konfirmasi yang menunggu tindakan Anda saat ini.
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('laporan.index', ['buka' => 'modalLaporan']) }}" class="bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] font-bold py-3 px-6 rounded-xl whitespace-nowrap">Buat Laporan Baru</a>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 mb-3 font-bold text-lg">{{ $laporanTerbuka }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Laporan Aktif</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Dalam Lab Anda</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center text-yellow-500 mb-3 font-bold text-lg">{{ $sedangDikerjakan }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Perbaikan</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Sedang Dikerjakan</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-500 mb-3 font-bold text-lg">{{ $perluDikonfirmasi->count() }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Konfirmasi</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Butuh Tindakan Anda</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-500 mb-3 font-bold text-lg">{{ $laporanSelesai }}</div>
                        <h3 class="text-xl font-bold text-[#2B4885]">Selesai</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Total Sepanjang Waktu</p>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Status Laporan Laboratorium</h3>
                        <a href="{{ route('laporan.index') }}" class="text-sm font-semibold text-blue-500 hover:text-blue-700">Lihat Semua</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase">
                                    <th class="py-4">Kode / Fasilitas</th>
                                    <th class="py-4">Kendala</th>
                                    <th class="py-4">Status Progres</th>
                                    <th class="py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-50">
                                @forelse ($daftarLaporan as $l)
                                    @php
                                        $label = ucfirst($l->status_laporan);
                                        $warna = config('simantap.warna_laporan.' . $l->status_laporan);
                                    @endphp

                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 font-bold text-gray-800">
                                            {{ $l->kode_laporan }}
                                            <br>
                                            <span class="text-xs font-normal text-gray-400">
                                                {{ $l->inventaris->nama_barang ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="py-4 text-gray-600">
                                            {{ \Illuminate\Support\Str::limit($l->kerusakan, 40) }}
                                        </td>

                                        <td class="py-4">
                                            <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full {{ $warna }}">
                                                {{ $label }}
                                            </span>
                                        </td>

                                        <td class="py-4">
                                            <a
                                                href="{{ route('laporan.show', $l->laporan_id) }}"
                                                class="inline-block bg-gray-100 text-gray-500 text-xs font-bold py-2 px-4 rounded-lg"
                                            >
                                                Lihat Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-gray-400">
                                            Tidak ada laporan aktif di lab Anda saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                @foreach ($infoLab as $lab)
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-[#2B4885] mb-4">Informasi Ruangan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Ruangan:</span>
                                <span class="font-bold text-gray-800">{{ $lab->nama_ruangan }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Total Inventaris:</span>
                                <span class="font-bold text-gray-800">{{ $lab->total }} Unit Barang</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Kondisi Baik:</span>
                                <span class="font-bold text-emerald-600">{{ $lab->baik }} Unit</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Dalam Perbaikan:</span>
                                <span class="font-bold text-amber-600">{{ $lab->dalamPerbaikan }} Unit</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($infoLab->isEmpty())
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 text-sm text-gray-400 text-center">
                        Akun ini belum ditetapkan sebagai PJ ruangan laboratorium mana pun.
                    </div>
                @endif

                @include('dashboard.konfirmasi-pj')

            </div>
        </div>
    </main>
</body>
</html>
