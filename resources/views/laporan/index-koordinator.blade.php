<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pengajuan Laporan - Koordinator Sarpras - SIMANTAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fb; }
        .modal-backdrop { background-color: rgba(15, 23, 42, 0.45); }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-simantap.css') }}">
</head>
<body class="min-h-screen text-gray-800">

    @include('partials.navbar-internal')

    <main class="max-w-[1400px] mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-[#2B4885]">Pengajuan Laporan & Rekomendasi</h2>
                <p class="text-sm text-gray-500 mt-1">Tinjau rekomendasi petugas, tetapkan prioritas, dan pantau seluruh laporan. Koordinator tidak membuat laporan baru maupun mengubah isi laporan pelapor.</p>
            </div>
            <div class="bg-blue-50 border border-blue-100 text-[#2B4885] text-xs font-semibold px-4 py-2.5 rounded-xl">
                Menampilkan seluruh laporan dari semua ruangan
            </div>
        </div>

        @if (session('sukses'))
            <div class="bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-xl px-4 py-3 mb-6">{{ session('sukses') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-6">
                <p class="font-semibold">
                    Perubahan belum berhasil disimpan:
                </p>

                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between gap-4">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari berdasarkan kode, barang, atau ruangan..." class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm w-full md:w-96 focus:outline-none focus:border-[#2B4885]">
            <div class="flex items-center space-x-3">
                <select name="status" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-600 font-medium focus:outline-none">
        <option value="">Semua Status</option>
        @foreach (config('simantap.status') as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
        </select>
                <select name="prioritas" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-600 font-medium focus:outline-none">
                    <option value="">Semua Prioritas</option>
                    <option value="tinggi" @selected(request('prioritas')==='tinggi')>Tinggi</option>
                    <option value="rendah" @selected(request('prioritas')==='rendah')>Rendah</option>
                    <option value="belum" @selected(request('prioritas')==='belum')>Belum Ditetapkan</option>
                </select>
                <button type="submit" class="bg-[#2B4885] text-white text-sm font-bold px-4 py-2.5 rounded-xl">Cari</button>
            </div>
        </form>

        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-2">Kode Laporan</th>
                            <th class="py-4 px-2">Barang / Ruangan</th>
                            <th class="py-4 px-2">Tanggal Lapor</th>
                            <th class="py-4 px-2">Prioritas</th>
                            <th class="py-4 px-2">Status Laporan</th>
                            <th class="py-4 px-2">Perkembangan Laporan</th>
                            <th class="py-4 px-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-50">
                        @forelse ($baris as $item)
                            @php $lap = $item['laporan']; @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $item['aksi'] === 'tinjau' ? 'bg-blue-50/20' : ($item['aksi'] === 'tutup' ? 'bg-emerald-50/20' : '') }}">
                                <td class="py-4 px-2 font-bold text-gray-800">{{ $lap->kode_laporan }}</td>
                                <td class="py-4 px-2">
                                    <p class="font-medium text-gray-800">{{ $lap->inventaris->nama_barang ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $lap->ruangan->nama_ruangan ?? '-' }}</p>
                                </td>
                                <td class="py-4 px-2 text-gray-600 font-medium">{{ $lap->tanggal_laporan->format('d M Y') }}</td>
                                <td class="py-4 px-2">
                                    @if ($lap->prioritas === 'tinggi')
                                        <span class="bg-red-50 text-red-700 text-xs font-bold px-3 py-1.5 rounded-full">Tinggi</span>
                                    @elseif ($lap->prioritas === 'rendah')
                                        <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-full">Rendah</span>
                                    @else
                                        <span class="text-xs text-gray-400">Belum ditetapkan</span>
                                    @endif
                                </td>
                                <td class="py-4 px-2">
                                    <span class="{{ $item['warna_status'] }} text-xs font-bold px-3 py-1.5 rounded-full">{{ $item['label_status'] }}</span>
                                    @if ($item['aksi'] === 'tutup')
                                        <p class="text-[10px] text-gray-500 mt-1">Pelapor sudah mengonfirmasi hasil. Siap ditutup.</p>
                                    @endif
                                </td>
                                <td class="py-4 px-2 text-xs font-medium {{ $item['aksi'] === 'tinjau' ? 'text-[#2B4885] font-bold' : 'text-gray-500 italic' }}">
                                    {{ $item['rekomendasi_text'] }}
                                </td>
                                <td class="py-4 px-2 text-center space-x-1 flex justify-center">
                                    <button onclick="openModal('modalDetail-{{ $lap->laporan_id }}')" class="bg-white border border-gray-200 text-gray-500 text-xs font-bold py-2 px-3 rounded-lg">Detail</button>
                                    @if ($item['aksi'] === 'tinjau')
                                        <button onclick="openModal('modalTinjau-{{ $lap->laporan_id }}')" class="bg-[#F5C518] hover:bg-[#e3b60f] text-[#2B4885] text-xs font-extrabold py-2 px-3 rounded-lg shadow-sm">Tinjau Rekomendasi</button>
                                    @elseif ($item['aksi'] === 'penugasan')
                                        <a href="{{ route('penugasan.index') }}" class="bg-[#2B4885] hover:bg-[#213868] text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm">Buka Penugasan</a>
                                    @elseif ($item['aksi'] === 'tutup')
                                        <button onclick="openModal('modalTutup-{{ $lap->laporan_id }}')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold py-2 px-3 rounded-lg shadow-sm">Tutup Laporan</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-10 text-center text-sm text-gray-400">Belum ada laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $baris->links() }}</div>
        </div>
    </main>

    @foreach ($baris as $item)
        @php $lap = $item['laporan']; $pem = $item['pemeriksaan_terakhir']; $tin = $item['tindakan_terakhir']; $kon = $item['konfirmasi_terakhir']; @endphp

        @if ($item['aksi'] === 'tinjau' && $pem)
            <div id="modalTinjau-{{ $lap->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-6 modal-backdrop overflow-y-auto">
                <div class="bg-white w-full max-w-xl rounded-[1.75rem] shadow-xl p-8 my-auto">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Tinjau Rekomendasi</h3>
                        <button onclick="closeModal('modalTinjau-{{ $lap->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-6">{{ $lap->kode_laporan }} &middot; {{ $lap->inventaris->nama_barang ?? '-' }} &middot; {{ $lap->ruangan->nama_ruangan ?? '-' }}</p>

                    <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-2 border border-gray-100 text-sm">
                        <p><span class="text-gray-400">Petugas:</span> <span class="font-semibold">{{ $pem->petugas->nama ?? '-' }}</span></p>
                        <p><span class="text-gray-400">Temuan:</span> {{ $pem->temuan ?? '-' }}</p>
                        @if ($pem->rekomendasi === 'penggantian')
                            <p><span class="text-gray-400">Alasan penggantian:</span> {{ $pem->alasan_penggantian ?: 'Belum diisi. Minta revisi sebelum menyetujui.' }}</p>
                        @endif
                        <p><span class="text-gray-400">Rekomendasi Petugas:</span> <span class="font-semibold">{{ $pem->label_rekomendasi }}</span>
                            @if ($pem->sumber_pengganti) &middot; {{ $pem->sumber_pengganti === 'stok_gudang' ? 'Ambil Stok Gudang' : 'Pengadaan Baru' }} @endif
                        </p>
                    </div>

                    <form action="{{ route('laporan.tinjau', $pem->pemeriksaan_id) }}" method="POST" class="space-y-4" onsubmit="return validasiTinjau(this)">
                        @csrf
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Keputusan</label>
                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <label class="flex items-center justify-center gap-1.5 border border-gray-200 rounded-xl px-2 py-2.5 text-xs font-bold cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50 has-[:checked]:text-green-700">
                                    <input type="radio" name="keputusan" value="setuju" checked onchange="toggleCatatanWajib({{ $lap->laporan_id }}, false)" class="accent-green-600"> Setujui
                                </label>
                                <label class="flex items-center justify-center gap-1.5 border border-gray-200 rounded-xl px-2 py-2.5 text-xs font-bold cursor-pointer has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50 has-[:checked]:text-yellow-700">
                                    <input type="radio" name="keputusan" value="revisi" onchange="toggleCatatanWajib({{ $lap->laporan_id }}, true)" class="accent-yellow-500"> Minta Revisi
                                </label>
                                <label class="flex items-center justify-center gap-1.5 border border-gray-200 rounded-xl px-2 py-2.5 text-xs font-bold cursor-pointer has-[:checked]:border-red-500 has-[:checked]:bg-red-50 has-[:checked]:text-red-600">
                                    <input type="radio" name="keputusan" value="hentikan" onchange="toggleCatatanWajib({{ $lap->laporan_id }}, true)" class="accent-red-500"> Hentikan Laporan
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Catatan Koordinator <span id="catatanWajibTag-{{ $lap->laporan_id }}" class="hidden text-red-500 normal-case">(wajib diisi)</span></label>
                            <textarea name="catatan_koordinator" rows="3" placeholder="Wajib diisi jika Jelaskan perbaikan rekomendasi atau alasan penghentian laporan..." class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Prioritas Laporan</label>
                            @if ($lap->prioritas === null)
                                <select name="prioritas" required class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885] disabled:opacity-50">
                                    <option value="">Pilih prioritas saat menyetujui</option>
                                    <option value="rendah">Rendah</option>
                                    <option value="tinggi">Tinggi</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-2">Prioritas ditetapkan sekali ketika rekomendasi disetujui. Revisi atau penghentian tidak menetapkan prioritas.</p>
                            @else
                                <p class="mt-2 text-sm font-bold text-[#2B4885]">{{ ucfirst($lap->prioritas) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Prioritas sudah ditetapkan dan tetap berlaku pada pemeriksaan ulang.</p>
                            @endif
                        </div>
                        <div class="bg-blue-50 text-[#2B4885] text-[11px] p-3 rounded-xl border border-blue-100">
                            Jika disetujui, status laporan menjadi Disetujui. Penugasan pelaksana dapat dilakukan setelah syarat prioritas dan dana terpenuhi. Revisi dikembalikan kepada petugas pemeriksa yang sama. Hentikan Laporan mengakhiri proses tanpa penanganan dan wajib disertai alasan.
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeModal('modalTinjau-{{ $lap->laporan_id }}')" class="text-gray-500 hover:text-gray-700 text-sm font-bold py-2.5 px-4 rounded-xl">Batal</button>
                            <button type="submit" class="bg-[#2B4885] hover:bg-[#213868] text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm">Simpan Keputusan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($item['aksi'] === 'tutup')
            <div id="modalTutup-{{ $lap->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-6 modal-backdrop overflow-y-auto">
                <div class="bg-white w-full max-w-xl rounded-[1.75rem] shadow-xl p-8 my-auto">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="text-xl font-extrabold text-emerald-700">Tutup Laporan</h3>
                        <button onclick="closeModal('modalTutup-{{ $lap->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-6">{{ $lap->kode_laporan }} &middot; {{ $lap->inventaris->nama_barang ?? '-' }} &middot; {{ $lap->ruangan->nama_ruangan ?? '-' }}</p>

                    <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-3 border border-gray-100">
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase">Hasil Pekerjaan Terakhir</p>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $tin->hasil ?? '-' }} @if($tin?->petugas) &middot; {{ $tin->petugas->nama }} @endif</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Konfirmasi Pelapor</p>
                                <p class="text-sm font-bold text-emerald-700 mt-0.5">Sesuai @if($kon) &middot; {{ $kon->tanggal_konfirmasi->format('d M Y') }} @endif</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Catatan Pelapor</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $kon->catatan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 mb-5">
                        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700">
                            <span class="w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]">&#10003;</span>
                            Konfirmasi pelapor terakhir berstatus "Sesuai"
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700">
                            <span class="w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]">&#10003;</span>
                            Tidak ada pemeriksaan/pekerjaan lanjutan yang masih aktif
                        </div>
                    </div>

                    <form action="{{ route('laporan.tutup', $lap->laporan_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-emerald-50 text-emerald-800 text-[11px] p-3 rounded-xl border border-emerald-100 mb-4">
                            Setelah ditutup, status laporan otomatis berubah menjadi <strong>Selesai</strong> dan tanggal ditutup terisi waktu saat ini. Laporan yang sudah ditutup tidak dapat dibuka kembali secara manual.
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeModal('modalTutup-{{ $lap->laporan_id }}')" class="text-gray-500 hover:text-gray-700 text-sm font-bold py-2.5 px-4 rounded-xl">Batal</button>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-extrabold py-2.5 px-5 rounded-xl shadow-sm">Tutup Laporan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div id="modalDetail-{{ $lap->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-6 modal-backdrop overflow-y-auto">
            <div class="bg-white w-full max-w-2xl rounded-[1.75rem] shadow-xl p-8 my-auto relative">
                @include('partials.penghentian-laporan', ['laporan' => $lap])

                <div class="flex justify-between items-start mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Detail Riwayat Laporan ({{ $lap->kode_laporan }})</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $lap->inventaris->nama_barang ?? '-' }} &middot; {{ $lap->ruangan->nama_ruangan ?? '-' }} &middot; Status Saat Ini: <span class="font-bold text-gray-800">{{ $item['label_status'] }}</span></p>
                    </div>
                    <button onclick="closeModal('modalDetail-{{ $lap->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2"><span class="bg-gray-300 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">1</span> Laporan Awal</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Kerusakan</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $lap->kerusakan }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Pelapor &amp; Tanggal</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $lap->pelapor->nama ?? '-' }} &middot; {{ $lap->tanggal_laporan->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($pem)
                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <h4 class="text-sm font-bold text-[#2B4885] mb-3 flex items-center gap-2"><span class="bg-[#2B4885] text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">2</span> Pemeriksaan &amp; Keputusan Koordinator</h4>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Temuan Petugas</p>
                                    <p class="text-sm text-gray-700 mt-0.5">{{ $pem->temuan ?? '-' }} @if($pem->tanggal_pemeriksaan) (Diperiksa: {{ $pem->tanggal_pemeriksaan->format('d M Y') }}) @endif</p>
                                    @if ($pem->alasan_penggantian)
                                        <p class="text-sm mt-2">Alasan penggantian: {{ $pem->alasan_penggantian }}</p>
                                    @endif
                                @if ($pem->alasan_penggantian)
                                    <p class="text-sm mt-2">Alasan penggantian: {{ $pem->alasan_penggantian }}</p>
                                @endif
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase">Rekomendasi</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $pem->label_rekomendasi }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase">Keputusan Anda</p>
                                        <p class="text-sm font-bold mt-0.5 {{ $pem->status_persetujuan === 'disetujui' ? 'text-green-600' : ($pem->status_persetujuan === 'dihentikan' ? 'text-red-600' : 'text-gray-500') }}">
                                            {{ ucfirst(str_replace('_',' ', $pem->status_persetujuan)) }} @if($lap->prioritas) &middot; Prioritas: {{ ucfirst($lap->prioritas) }} @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($tin)
                        <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                            <h4 class="text-sm font-bold text-purple-800 mb-3 flex items-center gap-2"><span class="bg-purple-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">3</span> Pelaksanaan (Penugasan)</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Hasil Penanganan</p>
                                    <p class="text-sm text-gray-700 mt-0.5">{{ $tin->hasil ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Petugas Pelaksana</p>
                                    <p class="text-sm text-gray-700 mt-0.5">{{ $tin->petugas->nama ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($lap->status_laporan === 'selesai')
                        <div class="bg-green-50/50 p-4 rounded-xl border border-green-200">
                            <h4 class="text-sm font-bold text-green-800 mb-3 flex items-center gap-2"><span class="bg-green-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">4</span> Penyelesaian</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Konfirmasi Pelapor</p>
                                    <p class="text-sm font-bold text-green-700 mt-0.5">{{ $kon->hasil_konfirmasi === 'sesuai' ? 'Sesuai' : 'Masih Bermasalah' }} @if($kon?->catatan) ({{ $kon->catatan }}) @endif</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Tanggal Selesai (Ditutup)</p>
                                    <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $lap->tanggal_ditutup?->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 text-gray-400 text-[11px] p-3 rounded-xl mt-6 text-center">
                    Halaman ini hanya untuk peninjauan. Isi laporan awal, temuan, dan hasil pekerjaan tidak dapat diubah dari sini.
                </div>
                <div class="flex justify-end pt-4">
                    <button type="button" onclick="closeModal('modalDetail-{{ $lap->laporan_id }}')" class="bg-white border border-gray-200 hover:border-[#2B4885] text-[#2B4885] text-sm font-bold py-2.5 px-6 rounded-xl">Tutup Jendela</button>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function toggleCatatanWajib(laporanId, wajib) {
            const modal = document.getElementById('modalTinjau-' + laporanId);
            const catatan = modal.querySelector('textarea[name="catatan_koordinator"]');
            const prioritas = modal.querySelector('select[name="prioritas"]');

            document.getElementById('catatanWajibTag-' + laporanId).classList.toggle('hidden', !wajib);
            catatan.required = wajib;

            if (prioritas) {
                prioritas.disabled = wajib;
                prioritas.required = !wajib;
            }
        }
        function validasiTinjau(form) {
            const keputusan = form.querySelector('input[name="keputusan"]:checked').value;
            const catatan = form.querySelector('textarea[name="catatan_koordinator"]').value.trim();
            if (keputusan !== 'setuju' && catatan === '') {
                alert('Catatan koordinator wajib diisi untuk revisi atau penghentian.');
                return false;
            }
            return true;
        }
    </script>
<script>
        const requestedModal = @json(request('buka'));
        if (typeof requestedModal === 'string' && /^(modalLaporan|modal(?:Pemeriksaan|Tinjau|Konfirmasi|Tutup)-[0-9]+)$/.test(requestedModal)) {
            const modal = document.getElementById(requestedModal);
            if (modal) modal.classList.remove('hidden');
        }
    </script>
</body>
</html>
